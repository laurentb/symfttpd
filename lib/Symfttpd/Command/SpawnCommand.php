<?php
/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd\Command;

use Symfttpd\Symfttpd;
use Symfttpd\MultiTail;
use Symfttpd\Tail;
use Symfttpd\PosixTools;
use Symfttpd\Configuration\LighttpdConfiguration;
use Symfttpd\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;


/**
 * SpawnCommand class
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * @todo Use Spork instead of native php function to creae a fork (mocking...)
 */
class SpawnCommand extends Command
{
    /**
     * @var string
     */
    protected $server;

    protected function configure()
    {
        $this->setName('spawn');
        $this->setDescription('Launch the webserver.');

        $this->addOption('default', null, InputOption::VALUE_OPTIONAL, 'Change the default application.', 'index');
        $this->addOption('only', null, InputOption::VALUE_OPTIONAL, 'Do not allow any other application.', false);
        $this->addOption('allow', null, InputOption::VALUE_OPTIONAL, 'Useful with `only`, allow some other applications (useful for allowing a _dev alternative, for example).', false);
        $this->addOption('nophp', null, InputOption::VALUE_OPTIONAL, 'Deny PHP execution in the specified directories (default being uploads).', 'uploads');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path of the web directory. Autodected to ../web if not present.', getcwd() . '/web');
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to listen', 4042);
        $this->addOption('bind', 'b', InputOption::VALUE_OPTIONAL, 'The address to bind', '127.0.0.1');
        $this->addOption('all', 'a', InputOption::VALUE_NONE, 'Bind on all addresses');
        $this->addOption('tail', 't', InputOption::VALUE_NONE, 'Print the log in the console');
        $this->addOption('kill', 'K', InputOption::VALUE_NONE, 'Kill existing running symfttpd');

        if (function_exists('pcntl_fork')) {
            $this->addOption('single_process', 's', InputOption::VALUE_OPTIONAL, 'Run symfttpd in another process', false);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>Symfttpd - version %s</comment>', Symfttpd::VERSION));

        // Kill other symfttpd process.
        if ($input->getOption('kill')) {
            // Kill existing symfttpd instance if found.
            if (file_exists($this->getRestartfile())) {
                unlink($input->getRestartfile());
            }
            exit(!PosixTools::killPid($this->getPidfile(), $output));
        }

        $this->server = $this->getConfiguration()->get('server', 'lighttpd');

        // Initialise Server options
        $serverConfiguration = new LighttpdConfiguration($this->getProjectPath());
        $serverConfiguration->clear();
        $serverConfiguration->set('port', $input->getOption('port'));
        $serverConfiguration->set('bind', $input->getOption('bind'));

        $this->getConfiguration()->set('restartfile', $serverConfiguration->getCacheDir().'/.symfttpd_restart');

        $allow = explode(',', $input->getOption('allow'));
        $nophp = explode(',', $input->getOption('nophp'));
        $path  = realpath($input->getOption('path'));

        $files = array(
            'dir' => array(),
            'php' => array(),
            'file' => array()
        );

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('Document root "%s" not found.', $input->getOption('path')));
        }

        foreach (new \DirectoryIterator($path) as $file) {
            $name = $file->getFilename();
            if ($name[0] != '.') {
                if ($file->isDir()) {
                    $files['dir'][] = $name;
                }
                elseif (!preg_match('/\.php$/', $name)) {
                    $files['file'][] = $name;
                }
                elseif (empty($options['only'])) {
                    $files['php'][] = $name;
                }
            }
        }

        foreach ($allow as $name) {
            $files['php'][] = $name . '.php';
        }

        $serverConfiguration->add(array(
            'document_root' => $path,
            'default' => $input->getOption('default'),
            'nophp'   => $nophp,
            'phps'    => $files['php'],
            'files'   => $files['file'],
            'dirs'    => $files['dir'],
        ));

        // Creates the server configuration.
        $serverConfiguration->generate($this->getConfiguration());
        $serverConfiguration->write();

        $boundAddress = $input->getOption('all') ? 'all-interfaces' : $input->getOption('bind');

        $bind = $input->getOption('all') ? : $input->getOption('bind');
        $host = in_array($bind, array(false, '0.0.0.0', '::'), true) ? 'localhost' : $bind;

        $apps = array();
        foreach ($serverConfiguration->get('phps') as $file) {
            if (preg_match('/.+\.php$/', $file)) {
                $apps[$file] = ' http://' . $host . ':' . $input->getOption('port') . '/<info>' . $file . '</info>';
            }
        }

        // Pretty information. Nothing interesting code-wise.
        $text = <<<TEXT
lighttpd started on <info>%s</info>, port <info>%s</info>.

Available applications:
%s

Press Ctrl+C to stop serving.

TEXT;
        $output->write(sprintf($text, $boundAddress, $serverConfiguration->get('port'), implode("\n", $apps)));

        flush();

        if (true == $input->getOption('single_process')) {
            // Run lighttpd
            $this->serverStart($serverConfiguration);

            $output->write('Terminated.');
        } else {
            if ($input->getOption('tail')) {
                $logDir = $this->getProjectPath() . $serverConfiguration->getLogDir();
                $multitail = new MultiTail();
                $multitail->add('access', new Tail($logDir . '/access.log'),
                    Symfttpd\Color::fgColor('blue'), Symfttpd\Color::style('normal'));
                $multitail->add(' error', new Tail($logDir . '/error.log'),
                    Symfttpd\Color::style('bright') . Symfttpd\Color::fgColor('red'), Symfttpd\Color::style('normal'));
                // We have to do it before the fork to capture the startup messages
                $multitail->consume();
            }
            $pid = pcntl_fork();
            if ($pid) {
                // Parent process
                $prevGenconf = null;
                while (false !== sleep(1)) {

                    // Generate the configuration file.
                    if (false == $this->getConfiguration()->has('genconf_cmd')) {
                        $serverConfiguration->generateHost($this->getConfiguration());
                    }

                    $genconf = $serverConfiguration->read();

                    if ($prevGenconf !== null && $prevGenconf !== $genconf) {
                        // This sleep() is so that if a HTTP request just created a file in web/,
                        // the web server isn't restarted right away.
                        sleep(1);
                        touch($this->getRestartfile());
                        !PosixTools::killPid($this->getPidfile(), $output);
                    }
                    $prevGenconf = $genconf;

                    if ($input->getOption('tail')) {
                        $multitail->consume();
                    }

                    // If the children is defunct, we are finished here
                    if (pcntl_waitpid($pid, $status, WNOHANG)) {
                        exit(0);
                    }
                }
            } elseif ($pid == 0) {
                // Child process
                do {
                    if (file_exists($this->getRestartfile())) {
                        unlink($this->getRestartfile());
                    }

                    // Run lighttpd
                    $this->serverStart($serverConfiguration);

                    if (!file_exists($this->getRestartfile())) {
                        $output->writeln('Terminated.');
                    } else {
                        $output->writeln('<info>Something in web/ changed. Restarting lighttpd.</info>');

                        // Regenerate the lighttpd configuration
                        $serverConfiguration->generateHost($this->getConfiguration());
                    }
                } while (file_exists($this->getRestartfile()));
            }
            else {
                $input->writeln('<error>Unable to fork!</error>');
                exit(1);
            }
        }
    }

    /**
     * Return the project path.
     *
     * @return string
     */
    protected function getProjectPath()
    {
        return getcwd();
    }

    /**
     * Start the server.
     *
     * @param \Symfttpd\Configuration\ServerConfigurationInterface $serverConfiguration
     */
    protected function serverStart(\Symfttpd\Configuration\ServerConfigurationInterface $serverConfiguration)
    {
        passthru($this->getConfiguration()->getServerCmd() . ' -D -f ' . escapeshellarg($serverConfiguration->getConfigFile()));
    }

    /**
     * @return \Symfttpd\Configuration\SymfttpdConfiguration
     */
    public function getConfiguration()
    {
        return $this->getApplication()->getSymfttpd()->getConfiguration();
    }
}

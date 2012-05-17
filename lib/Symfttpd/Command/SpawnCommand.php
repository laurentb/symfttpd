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
use Symfttpd\Tail\MultiTail;
use Symfttpd\Tail\Tail;
use Symfttpd\Tail\TailInterface;
use Symfttpd\Console\Application;
use Symfttpd\Command\Command;
use Symfttpd\Server\ServerInterface;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;


use Symfttpd\Project\Symfony14;

/**
 * SpawnCommand class
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
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

        // Spawning options
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to listen', 4042)
            ->addOption('bind', 'b', InputOption::VALUE_OPTIONAL, 'The address to bind', '127.0.0.1')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Bind on all addresses')
            ->addOption('tail', 't', InputOption::VALUE_NONE, 'Print the log in the console')
            ->addOption('kill', 'K', InputOption::VALUE_NONE, 'Kill existing running symfttpd');

        if (function_exists('pcntl_fork')) {
            $this->addOption('single_process', 's', InputOption::VALUE_OPTIONAL, 'Run symfttpd in another process', false);
        }
    }

    /**
     * Run the Symttpd configured server.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $this->getSymfttpd()->getServer();
        $server->options->add(array(
            // Lighttpd options
            'port' => $input->getOption('port'),
            'bind' => true == $input->getOption('all') ? false : $input->getOption('bind')
        ));

        // Kill other running server in the current project.
        if (true == $input->getOption('kill')) {
            // Kill existing symfttpd instance if found.
            $server->removeRestartFile();
            \Symfttpd\Utils\PosixTools::killPid($server->getPidfile(), $output);
        }

        // Creates the server configuration.
        $this->getSymfttpd()->getProject()->initialize();
        $server->setup();
        $server->generate($this->getConfiguration());
        $server->write();

        if (false == $server->options->get('bind')) {
            $boundAddress = 'all-interfaces';
        } else {
            $boundAddress = $server->options->get('bind');
        }

        $bind = $server->options->get('bind');
        $host = in_array($bind, array(false, '0.0.0.0', '::'), true) ? 'localhost' : $bind;

        $apps = array();
        foreach ($this->getSymfttpd()->getProject()->readablePhpFiles as $file) {
            if (preg_match('/.+\.php$/', $file)) {
                $apps[$file] = ' http://' . $host . ':' . $server->options->get('port') . '/<info>' . $file . '</info>';
            }
        }

        // Pretty information. Nothing interesting code-wise.
        $text = <<<TEXT
lighttpd started on <info>%s</info>, port <info>%s</info>.

Available applications:
%s

<important>Press Ctrl+C to stop serving.</important>

TEXT;
        $output->getFormatter()->setStyle('important', new OutputFormatterStyle('yellow', null, array('bold')));
        $output->write(sprintf($text, $boundAddress, $server->options->get('port'), implode("\n", $apps)));

        flush();

        if (true == $input->getOption('single_process')) {
            $output->writeln('<info>Symfttpd will run in a single process mode.</info>');
            // Run lighttpd
            $server->start();

            $output->write('Terminated.');

            return 0;
        }

        $multitail = null;
        if ($input->getOption('tail')) {
            $logDir = $server->options->get('log_dir');
            $multitail = new MultiTail(new OutputFormatter(true));
            $multitail->add('access', new Tail($logDir . '/access.log'), new OutputFormatterStyle('blue'));
            $multitail->add('error', new Tail($logDir . '/error.log'), new OutputFormatterStyle('red', null, array('bold')));
            // We have to do it before the fork to capture the startup messages
            $multitail->consume();
        }

        $pid = pcntl_fork();
        $process = null;
        if ($pid) {
            $this->watch($server, $output, $multitail);
            if (pcntl_waitpid($pid, $status, WNOHANG))
            {
                exit(0);
            }
        } else if (0 === $pid) {
            // Child process
            $this->spawn($server, $output);
        } else {
            $output->writeln('<error>Could not fork</error>');

            exit(1);
        }

        return 0;
    }

    /**
     * Launch the server in a fork.
     * The parent thread check every seconds if the rewrite
     * rules changed. In this case it will create a file that
     * will tell to the fork that the server must be restarted.
     *
     * @param \Symfttpd\Server\ServerInterface $server
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return bool
     */
    protected function spawn(ServerInterface $server, OutputInterface $output)
    {
        do {
            try {
                // Run lighttpd
                $server->start();
            } catch (\Exception $e) {
                $output->writeln('<error>The server cannot start</error>');
                $output->writeln(sprintf('<error>%s</error>', trim($e->getMessage(), " \0\t\r\n")));
            }

            if (!file_exists($server->getRestartFile())) {
                $output->writeln('Terminated.');

                return false;
            } else {
                $output->writeln('<comment>Something in web/ changed. Restarting lighttpd.</comment>');

                // Regenerate the lighttpd configuration
                $server->generateConfiguration($this->getSymfttpd()->getConfiguration());
                $server->writeConfiguration();
            }
        } while (file_exists($server->getRestartFile()));

        return false;
    }

    /**
     * Watch the document directory.
     * If the configuration of the server changed (a file has been added in the
     * web directory for instance), it creates the restart file used in the
     * spawn to tell it to restart the server.
     *
     * @param \Symfttpd\Server\ServerInterface $server
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface $multitail
     */
    public function watch(ServerInterface $server, OutputInterface $output, TailInterface $multitail = null)
    {
        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $prevGenconf = null;
        while (false !== sleep(1)) {
            // Generate the configuration file.
            $server->setup();
            $server->generateRules($this->getSymfttpd()->getConfiguration());
            $server->writeRules();
            $genconf = $server->readRules();

            if ($prevGenconf !== null && $prevGenconf !== $genconf) {
                // This sleep() is so that if a HTTP request just created a file in web/,
                // the web server isn't restarted right away.
                sleep(1);

                // Tell the child process to restart the server
                $filesystem->touch($server->getRestartFile());

                // Kill the current server process.
                \Symfttpd\Utils\PosixTools::killPid($server->getPidfile(), $output);
            }
            $prevGenconf = $genconf;

            if ($multitail instanceof MultiTail) {
                $multitail->consume();
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
     * @return \Symfttpd\Configuration\SymfttpdConfiguration
     */
    public function getConfiguration()
    {
        return $this->getSymfttpd()->getConfiguration();
    }
}

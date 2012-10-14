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

namespace Symfttpd\Server;

use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Tail\TailInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Config;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfttpd\Filesystem\Filesystem;
use Symfttpd\Loader;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\BaseServer;
use Symfttpd\Writer;

/**
 * Lighttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Lighttpd extends BaseServer
{
    /**
     * Server name.
     *
     * @var string
     */
    public $name = 'lighttpd';

    /**
     * The shell command to run lighttpd.
     *
     * @var string
     */
    protected $command;

    /**
     * The file that configures the server.
     *
     * @var string
     */
    protected $configFilename = 'lighttpd.conf';

    /**
     * The generated configuration used by lighttpd.
     *
     * @var string
     */
    protected $lighttpdConfig;

    /**
     * Constructor class
     *
     * @param \Symfttpd\Project\ProjectInterface $project
     * @param \Twig_Environment                  $twig
     * @param \Symfttpd\Loader                   $loader
     * @param \Symfttpd\Writer                   $writer
     * @param \Symfttpd\OptionBag                $config
     */
    public function __construct(ProjectInterface $project, \Twig_Environment $twig, Loader $loader, Writer $writer, Config $config)
    {
        parent::__construct($project, $twig, $loader, $writer, $config);

        // Add the lighttpd templates directory to twig loader.
        $this->twig->getLoader()->addPath(__DIR__ . '/../Resources/templates/lighttpd');

        $this->rotate();
    }

    /**
     * Return the project.
     *
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Return the pidfile which contains the pid of the process
     * of the server.
     *
     * @return mixed|null
     */
    public function getPidfile()
    {
        return $this->getCacheDir() . '/' . $this->config->get('server_pidfile', '.sf');
    }

    /**
     * Read the server configuration.
     *
     * @param string $separator
     *
     * @return string
     * @throws \Symfttpd\Exception\LoaderException
     */
    public function read()
    {
        if (null == $this->lighttpdConfig) {
            $this->lighttpdConfig = $this->loader->load($this->getConfigFile());
        }

        return $this->lighttpdConfig;
    }

    /**
     * Write the configurations files.
     *
     * @param string $type
     * @param bool   $force
     */
    public function write($force = false)
    {
        $this->writer->write($this->lighttpdConfig, $this->getConfigFile(), $force);
    }

    /**
     * Generate the whole configuration :
     * the server configuration based on the lighttpd.conf.php template
     * the rules configuration with the rewrite rules based on the rules.conf.php template
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generate()
    {
        $this->project->scan();

        $this->lighttpdConfig = $this->twig->render(
            $this->name . '/' . $this->configFilename . '.twig',
            array(
                'document_root' => $this->project->getWebDir(),
                'port'          => $this->config->get('port'),
                'bind'          => $this->config->get('bind', null),
                'error_log'     => $this->getLogDir() . '/' . $this->config->get('server_error_log', 'error.log'),
                'access_log'    => $this->getLogDir() . '/' . $this->config->get('server_access_log', 'access.log'),
                'pidfile'       => $this->getPidfile(),
                'php_cgi_cmd'   => $this->config->get('php_cgi_cmd'),
                'dirs'          => $this->project->readableDirs,
                'files'         => $this->project->readableFiles,
                'phps'          => $this->project->readablePhpFiles,
                'default'       => $this->project->getIndexFile(),
                'nophp'         => $this->config->get('project_nophp', array('uploads')),
            )
        );

        return $this->lighttpdConfig;
    }

    /**
     * Remove the log and cache directory of
     * lighttpd and recreate them.
     *
     * @param null|\Symfttpd\Filesystem\Filesystem $filesystem
     */
    public function rotate($clear = false, Filesystem $filesystem = null)
    {
        $directories = array(
            $this->getCacheDir(),
            $this->getLogDir(),
        );

        $filesystem = $filesystem ? : new Filesystem();

        if (true === $clear) {
            $filesystem->remove($directories);
        }
        $filesystem->mkdir($directories);
    }

    /**
     * Return the lighttpd configuration file path.
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->getCacheDir() . '/' . $this->configFilename;
    }

    /**
     * Return the name of the configuration file.
     *
     * @return string
     */
    public function getConfigFilename()
    {
        return $this->configFilename;
    }

    /**
     * Return the lighttpd cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->project->getCacheDir() . '/' . $this->name;
    }

    /**
     * Return the lighttpd log directory.
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->project->getLogDir() . '/' . $this->name;
    }

    /**
     * Return the server command value
     *
     * @param null|\Symfony\Component\Process\ExecutableFinder $finder
     *
     * @return string
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function getCommand(ExecutableFinder $finder = null)
    {
        if (null == $this->command) {

            if (null == $finder) {
                $finder = new ExecutableFinder();
            }

            $finder->addSuffix('');
            $cmd = $finder->find('lighttpd');

            if (null == $cmd) {
                throw new ExecutableNotFoundException('lighttpd executable not found.');
            }

            $this->command = $cmd;
        }

        return $this->command;
    }

    /**
     * Set the command to use.
     *
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Start the server.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return bool
     */
    public function start(OutputInterface $output, TailInterface $tail = null)
    {
        // Run lighttpd
        $this->doStart($output);

        $prevGenconf = null;
        while (false !== sleep(1)) {
            /**
             * Regenerate the configuration file. to check if it defers.
             * @todo check the web dir informations to detect any changes instead.
             */
            $genconf = $this->generate();

            if ($prevGenconf !== null && $prevGenconf !== $genconf) {
                // This sleep() is so that if a HTTP request just created a file in web/,
                // the web server isn't restarted right away.
                sleep(1);

                $this->stop($output);

                $output->writeln(sprintf('<comment>Something in web/ changed. Restarting %s.</comment>', $this->name));

                $this->doStart($output);
            }
            $prevGenconf = $genconf;

            if ($tail instanceof TailInterface) {
                $tail->consume();
            }
        }
    }

    /**
     * Start the server.
     *
     * @param $output
     */
    public function doStart($output)
    {
        try {
            // Regenerate the lighttpd configuration
            $this->generate();
            $this->write(true);

            $process = new \Symfony\Component\Process\Process(null);
            $process->setCommandLine($this->getCommand() . ' -f ' . escapeshellarg($this->getConfigFile()));
            $process->setWorkingDirectory($this->project->getRootDir());
            $process->setTimeout(null);
            $process->run();
        } catch (\Exception $e) {
            $output->writeln('<error>The server cannot start</error>');
            $output->writeln(sprintf('<error>%s</error>', trim($e->getMessage(), " \0\t\r\n")));
        }
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed|void
     */
    public function stop(OutputInterface $output)
    {
        // Kill the current server process.
        \Symfttpd\Utils\PosixTools::killPid($this->getPidfile(), $output);
    }
}

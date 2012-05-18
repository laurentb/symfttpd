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

use Symfttpd\Server\BaseServer;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Filesystem\Filesystem;
use Symfttpd\OptionBag;
use Symfttpd\Loader;
use Symfttpd\Writer;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Lighttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Lighttpd extends BaseServer
{
    /**
     * @var ProjectInterface
     */
    protected $project;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Symfttpd\Loader
     */
    protected $loader;

    /**
     * @var \Symfttpd\Writer
     */
    protected $writer;

    /**
     * The server options
     *
     * @var OptionBag
     */
    public $options;

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
     * The file that configures rewriting rules for lighttpd.
     *
     * @var string
     */
    protected $rulesFilename = 'rules.conf';

    /**
     * The generated rules.
     *
     * @var string
     */
    protected $rules;

    /**
     * Constructor class
     *
     * @param \Symfttpd\Project\ProjectInterface $project
     * @param \Twig_Environment $twig
     * @param \Symfttpd\Loader $loader
     * @param \Symfttpd\Writer $writer
     * @param \Symfttpd\OptionBag $options
     */
    public function __construct(ProjectInterface $project, \Twig_Environment $twig, Loader $loader, Writer $writer, OptionBag $options)
    {
        $this->project  = $project;
        $this->twig     = $twig;
        $this->options  = $options;
        $this->loader   = $loader;
        $this->writer   = $writer;

        // Add the lighttpd templates directory to twig loader.
        $this->twig->getLoader()->addPath(__DIR__.'/../Resources/templates/lighttpd');

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
     * Return the restartfile.
     *
     * If the server configuration (rules or base configuration)
     * changed, it generates a restart file that means that
     * the server must be restarted.
     *
     * @return mixed|null
     */
    public function getRestartFile()
    {
        return $this->getCacheDir().'/'.$this->options->get('restartfile', '.symfttpd_restart');
    }

    /**
     * Return the pidfile which contains the pid of the process
     * of the server.
     *
     * @return mixed|null
     */
    public function getPidfile()
    {
        return $this->getCacheDir().'/'.$this->options->get('restartfile', '.sf');
    }

    /**
     * Read the server configuration.
     *
     * @param string $separator
     * @return string
     * @throws \Symfttpd\Exception\LoaderException
     */
    public function read($separator = PHP_EOL)
    {
        return $this->readConfiguration() .
            $separator .
            $this->readRules();
    }

    /**
     * Return the lighttpd configuration content.
     * Read the lighttpd.conf in the cache file
     * if needed.
     *
     * @return string
     * @throws \Symfttpd\Exception\LoaderException
     */
    public function readConfiguration()
    {
        if (null == $this->lighttpdConfig) {
            $this->lighttpdConfig = $this->loader->load($this->getConfigFile());
        }

        return $this->lighttpdConfig;
    }

    /**
     * Return the rules configuration content.
     * Read the rules.conf in the cache directory
     * if needed.
     *
     * @return string
     * @throws \Symfttpd\Exception\LoaderException
     */
    public function readRules()
    {
        if (null == $this->rules) {
            $this->rules = $this->loader->load($this->getRulesFile());
        }

        return $this->rules;
    }

    /**
     * Write the configurations files.
     *
     * @param string $type
     * @param bool $force
     */
    public function write($type = 'all', $force = false)
    {
        switch ($type) {
            case 'config':
            case 'configuration':
                $this->writer->write(
                    $this->lighttpdConfig,
                    $this->getConfigFile(),
                    $force
                );
                break;
            case 'rules':
                $this->writer->write(
                    $this->rules,
                    $this->getRulesFile(),
                    $force
                );
                break;
            case 'all':
            default:
                $this->write('config', true);
                $this->write('rules', true);
                break;
        }
    }

    /**
     * Write the configuration file.
     *
     * @param bool $force
     */
    public function writeConfiguration($force = false)
    {
        $this->write('config', $force);
    }

    /**
     * Write the rules configuration file.
     *
     * @param bool $force
     */
    public function writeRules($force = false)
    {
        $this->write('rules', $force);
    }

    /**
     * Generate the whole configuration :
     * the server configuration based on the lighttpd.conf.php template
     * the rules configuration with the rewrite rules based on the rules.conf.php template
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generate(SymfttpdConfiguration $configuration)
    {
        $this->generateRules();
        $this->generateConfiguration($configuration);
    }

    /**
     * Generate the lighttpd configuration file.
     *
     * @param \Symfttpd\Configuration\SymfttpdConfiguration $configuration
     * @return string
     */
    public function generateConfiguration(SymfttpdConfiguration $configuration)
    {
        $this->lighttpdConfig = $this->twig->render(
            $this->name.'/'.$this->configFilename.'.twig',
            array(
                'document_root' => $this->project->getWebDir(),
                'port'          => $this->options->get('port'),
                'bind'          => $this->options->get('bind', null),
                'error_log'     => $this->getLogDir().'/'.$this->options->get('error_log', 'error.log'),
                'access_log'    => $this->getLogDir().'/'.$this->options->get('access_log', 'access.log'),
                'pidfile'       => $this->getPidfile(),
                'rules_file'    => null !== $this->rules ? $this->getRulesFile() : null,
                'php_cgi_cmd'   => $configuration->get('php_cgi_cmd'),
            )
        );

        return $this->lighttpdConfig;
    }

    /**
     * Generate the lighttpd rewrite rules.
     *
     * @return string
     */
    public function generateRules()
    {
        $this->project->scan();

        $this->rules = $this->twig->render(
            $this->name.'/'.$this->rulesFilename.'.twig',
            array(
                'dirs'    => $this->project->readableDirs,
                'files'   => $this->project->readableFiles,
                'phps'    => $this->project->readablePhpFiles,
                'default' => $this->project->getIndexFile(),
            )
        );

        return $this->rules;
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

        $filesystem = $filesystem ?: new Filesystem();

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
        return $this->getCacheDir().'/'.$this->configFilename;
    }

    /**
     * Return the rules config file path.
     *
     * @return string
     */
    public function getRulesFile()
    {
        return $this->getCacheDir().'/'.$this->rulesFilename;
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
     * Return the name of the rules file.
     *
     * @return string
     */
    public function getRulesFilename()
    {
        return $this->rulesFilename;
    }

    /**
     * Return the lighttpd cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->project->getCacheDir().'/'.$this->name;
    }

    /**
     * Return the lighttpd log directory.
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->project->getLogDir().'/'.$this->name;
    }

    /**
     * Return the server command value
     *
     * @param null|\Symfony\Component\Process\ExecutableFinder $finder
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
     * @param null|\Symfony\Component\Process\Process $process
     */
    public function start(\Symfony\Component\Process\Process $process = null)
    {
        // Remove an possible existing restart file
        if (file_exists($this->getRestartFile())) {
            unlink($this->getRestartFile());
        }

        if (null == $process) {
            $process = new \Symfony\Component\Process\Process(null);
        }

        $process->setCommandLine($this->getCommand() . ' -D -f ' . escapeshellarg($this->getConfigFile()));
        $process->setWorkingDirectory($this->project->getRootDir());
        $process->setTimeout(null);
        $process->run();
    }
}

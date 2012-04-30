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

use Symfttpd\Server\ServerInterface;
use Symfttpd\Filesystem\Filesystem;
use Symfttpd\Configuration\ConfigurationBag;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Configuration\ConfigurationInterface;
use Symfttpd\Configuration\Exception\ConfigurationException;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Lighttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Lighttpd implements ServerInterface
{
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
     * The path to the configuration file.
     *
     * @var string
     */
    protected $configFile;

    /**
     * The file that configures rewriting rules (mainly) for lighttpd.
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
     * The generated rules file used by lighttpd.
     *
     * @var string
     */
    protected $rulesFile;

    /**
     * The directory of the project.
     *
     * @var string
     */
    protected $workingDir;

    /**
     * The collection of configuration options.
     *
     * @var ConfigurationBag
     */
    public $configuration;

    /**
     * Constructor class
     *
     * @param null $workingDir
     * @param null|\Symfttpd\Configuration\ConfigurationBag $configuration
     */
    public function __construct($workingDir = null, ConfigurationBag $configuration = null)
    {
        $this->workingDir = $workingDir;
        $this->configuration = $configuration ?: new ConfigurationBag();

        // Set the defaults settings
        $this->configuration->set('log_dir', $this->workingDir . '/log/lighttpd');
        $this->configuration->set('cache_dir', $this->workingDir . '/cache/lighttpd');
        $this->configuration->set('pidfile', $this->getCacheDir().'/.sf');
    }

    /**
     * Read the server configuration.
     *
     * @return string
     */
    public function read()
    {
        return $this->readConfiguration().PHP_EOL.$this->readRules();
    }

    /**
     * Return the lighttpd configuration content.
     *
     * @return string
     * @throws Exception\ConfigurationException
     */
    public function readConfiguration()
    {
        if (null !== $this->lighttpdConfig) {
            return $this->lighttpdConfig;
        }

        if (false == file_exists($this->getConfigFile())) {
            throw new ConfigurationException('The lighttpd configuration has not been generated.');
        }

        return file_get_contents($this->getConfigFile());
    }

    /**
     * Return the rules configuration content.
     *
     * @return string
     * @throws Exception\ConfigurationException
     */
    public function readRules()
    {
        if (null !== $this->rules) {
            return $this->rules;
        }

        if (false == file_exists($this->rulesFile)) {
            throw new ConfigurationException('The rules configuration has not been generated.');
        }

        return file_get_contents($this->rulesFile);
    }

    /**
     * Write the configurations files.
     *
     * @throws Exception\ConfigurationException
     */
    public function write()
    {
        $type = count(func_get_args()) > 0 ? func_get_arg(0) : 'all';

        switch ($type) {
            case 'config':
            case 'configuration':
                $file = $this->configFile;
                $content = $this->lighttpdConfig;
                break;
            case 'rules':
                $file = $this->rulesFile;
                $content = $this->rules;
                break;
            case 'all':
            default:
                $this->write('config');
                $this->write('rules');
                break;
        }

        if ($type !== 'all' && false === file_put_contents($file, $content)) {
            throw new ConfigurationException(sprintf("Cannot generate the lighttpd %s file.", $type));
        }
    }

    /**
     * Write the configuration file.
     *
     * @throws Exception\ConfigurationException
     */
    public function writeConfiguration()
    {
        $this->write('config');
    }

    /**
     * Write the rules configuration file.
     *
     * @throws Exception\ConfigurationException
     */
    public function writeRules()
    {
        $this->write('rules');
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
        $this->generateRules($configuration);
        $this->generateConfiguration($configuration);
    }

    /**
     * Generate the lighttpd configuration file.
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generateConfiguration(SymfttpdConfiguration $configuration)
    {
        ob_start();
        require $this->getConfigurationTemplate();

        $this->lighttpdConfig = ob_get_clean();
        $this->configFile = $this->getCacheDir().DIRECTORY_SEPARATOR.$this->configFilename;
    }

    /**
     * Generate the lighttpd rules configuration.
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generateRules(SymfttpdConfiguration $configuration)
    {
        ob_start();
        require $this->getRulesTemplate();

        $this->rules = ob_get_clean();
        $this->rulesFile = $this->getCacheDir().DIRECTORY_SEPARATOR.$this->rulesFilename;
    }

    /**
     * Return the configuration template path.
     *
     * @return string
     */
    public function getConfigurationTemplate()
    {
        return __DIR__ . sprintf('/../Resources/templates/%s.php', $this->configFilename);
    }

    /**
     * Return the rules template path.
     *
     * @return string
     */
    public function getRulesTemplate()
    {
        return __DIR__ . sprintf('/../Resources/templates/%s.php', $this->rulesFilename);
    }

    /**
     * Return the lighttpd log directory.
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->configuration->get('log_dir');

    }

    /**
     * Return the lighttpd cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->configuration->get('cache_dir');
    }

    /**
     * Remove the log and cache directory of lighttpd.
     *
     * @param string $workingDir
     */
    public function clear()
    {
        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $directories = array(
            $this->workingDir . $this->getCacheDir(),
            $this->workingDir . $this->getLogDir(),
        );

        $filesystem->remove($directories);
        $filesystem->mkdir($directories);
    }

    /**
     * Return the lighttpd configuration file path.
     *
     * @return string
     */
    public function getConfigFile()
    {
        return $this->configFile;
    }

    /**
     * Return the rules config file path.
     *
     * @return string
     */
    public function getRulesFile()
    {
        return $this->rulesFile;
    }


    /**
     * Return the server command value
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function getCommand()
    {
        if (null == $this->command) {
            $exeFinder = new ExecutableFinder();
            $exeFinder->addSuffix('');
            $cmd = $exeFinder->find('lighttpd');

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
     */
    public function start()
    {
        passthru($this->getCommand() . ' -D -f ' . escapeshellarg($this->getConfigFile()));
    }
}

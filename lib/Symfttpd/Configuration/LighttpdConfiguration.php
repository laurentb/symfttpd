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

namespace Symfttpd\Configuration;

use Symfttpd\Filesystem\Filesystem;
use Symfttpd\Configuration\ConfigurationBag;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Configuration\ConfigurationInterface;
use Symfttpd\Configuration\ServerConfigurationInterface;
use Symfttpd\Configuration\Exception\ConfigurationException;

/**
 * LighttpdConfiguration class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdConfiguration extends ConfigurationBag implements ServerConfigurationInterface, ConfigurationInterface
{
    /**
     * @var string
     */
    protected $configFilename = 'lighttpd.conf';

    /**
     * @var string
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var string
     */
    protected $hostFilename = 'host.conf';

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $hostFile;

    /**
     * @var string
     */
    protected $workingDir;

    /**
     * Constructor class
     *
     * @param null $workingDir
     */
    public function __construct($workingDir = null)
    {
        $this->workingDir = $workingDir;

        $this->set('log_dir', $this->workingDir . '/log/lighttpd');
        $this->set('cache_dir', $this->workingDir . '/cache/lighttpd');
        $this->set('pidfile', $this->getCacheDir().'/.sf');
    }

    /**
     * Read the server configuration.
     *
     * @return string
     */
    public function read()
    {
        return $this->readConfiguration().PHP_EOL.$this->readHost();
    }

    /**
     * Return the lighttpd configuration content.
     *
     * @return string
     * @throws Exception\ConfigurationException
     */
    public function readConfiguration()
    {
        if (null !== $this->configuration) {
            return $this->configuration;
        }

        if (false == file_exists($this->getConfigFile())) {
            throw new ConfigurationException('The lighttpd configuration has not been generated.');
        }

        return file_get_contents($this->getConfigFile());
    }

    /**
     * Return the host configuration content.
     *
     * @return string
     * @throws Exception\ConfigurationException
     */
    public function readHost()
    {
        if (null !== $this->host) {
            return $this->host;
        }

        if (false == file_exists($this->hostFile)) {
            throw new ConfigurationException('The host configuration has not been generated.');
        }

        return file_get_contents($this->hostFile);
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
                $content = $this->configuration;
                break;
            case 'host':
                $file = $this->hostFile;
                $content = $this->host;
                break;
            case 'all':
            default:
                $this->write('config');
                $this->write('host');
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
     * Write the host configuration file.
     *
     * @throws Exception\ConfigurationException
     */
    public function writeHost()
    {
        $this->write('host');
    }

    /**
     * Generate the whole configuration :
     * the server configuration based on the lighttpd.conf.php template
     * the host configuration with the rewrite rules based on the host.conf.php template
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generate(SymfttpdConfiguration $configuration)
    {
        $this->generateHost($configuration);
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

        $this->configuration = ob_get_clean();
        $this->configFile = $this->getCacheDir().DIRECTORY_SEPARATOR.$this->configFilename;
    }

    /**
     * Generate the lighttpd host configuration.
     *
     * @param SymfttpdConfiguration $configuration
     */
    public function generateHost(SymfttpdConfiguration $configuration)
    {
        ob_start();
        require $this->getHostTemplate();

        $this->host = ob_get_clean();
        $this->hostFile = $this->getCacheDir().DIRECTORY_SEPARATOR.$this->hostFilename;
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
     * Return the host template path.
     *
     * @return string
     */
    public function getHostTemplate()
    {
        return __DIR__ . sprintf('/../Resources/templates/%s.php', $this->hostFilename);
    }

    /**
     * Return the lighttpd log directory.
     *
     * @return string
     */
    public function getLogDir()
    {
        return $this->get('log_dir');

    }

    /**
     * Return the lighttpd cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return $this->get('cache_dir');
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
     * Return the host config file path.
     *
     * @return string
     */
    public function getHostFile()
    {
        return $this->hostFile;
    }
}

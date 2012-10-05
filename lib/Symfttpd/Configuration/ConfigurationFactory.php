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

use Symfttpd\Configuration\ConfigurationInterface;
use Symfttpd\Configuration\ProjectConfiguration;
use Symfttpd\Configuration\ServerConfiguration;
use Symfttpd\OptionBag;

/**
 * ConfigurationFactory class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class ConfigurationFactory extends OptionBag
{
    /**
     * @var string
     */
    protected $filename = 'symfttpd.conf.php';

    /**
     * @var array
     */
    protected $paths;

    /**
     * Constructor
     *
     * @param array $options
     * @param array $paths
     */
    public function __construct(array $options = array(), array $paths = array())
    {
        $this->options = $options;

        $this->paths = array_merge(array(
            __DIR__.'/../Resources/templates/', // Resource directory
            getenv('HOME').'/.',  // user configuration
            getcwd().'/config/',  // project configuration
            getcwd().'/',
        ), $paths);

        foreach ($this->paths as $path) {
            $config = strpos($path, $this->filename) === false ? $path.$this->filename : $path;

            if (file_exists($config)) {
                require $config;
                if (isset($options)) {
                    $this->options = array_merge($options, $this->options);
                    unset($options);
                }
            }
        }
    }

    /**
     * Return a project configuration populated with
     * options set in the configuration file.
     *
     * @return ProjectConfiguration
     */
    public function createProjectConfiguration()
    {
        $configuration = new ProjectConfiguration();

        $this->prepare($configuration);

        return $configuration;
    }

    /**
     * Return a server configuration populated with
     * options set in the configuration file.
     *
     * @return ServerConfiguration
     */
    public function createServerConfiguration()
    {
        $configuration = new ServerConfiguration();

        $this->prepare($configuration);

        return $configuration;
    }

    /**
     * Set options of the configuration from the read file.
     *
     * @param ConfigurationInterface $configuration
     */
    protected function prepare(ConfigurationInterface $configuration)
    {
        foreach ($configuration->getKeys() as $key) {
            $configuration->set($key, $this->get($key));
        }
    }
}

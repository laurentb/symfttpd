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

use Symfttpd\Configuration\OptionBag;
use Symfttpd\Configuration\ConfigurationInterface;
use Symfttpd\Exception\ExecutableNotFoundException;

/**
 * SymfttpdConfiguration class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpdConfiguration extends OptionBag implements ConfigurationInterface
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
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;

        $this->paths = array(
            __DIR__.'/../Resources/templates/', // Resource directory
            getenv('HOME').'/.',  // user configuration
            getcwd().'/config/',  // project configuration
        );

        $this->read();
    }

    /**
     * Read the configuration file and return the options.
     */
    public function read()
    {
        foreach ($this->paths as $path) {
            $config = strpos($path, $this->filename) === false ? $path.$this->filename : $path;

            if (file_exists($config)) {
                require $config;
                if (isset($options)) {
                    $this->options = array_merge($this->options, $options);
                    unset($options);
                }
            }
        }
    }

    /**
     * Add a path to search the symfttpd
     * configuration file if it is not already
     * registered.
     *
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        if (false == array_search($path, $this->paths)) {
            $this->paths[] = $path;
        }
    }

    /**
     * @param array $paths
     * @return void
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }
}

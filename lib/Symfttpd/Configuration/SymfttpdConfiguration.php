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

/**
 * SymfttpdOption class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpdConfiguration implements ConfigurationInterface, \IteratorAggregate
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
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->paths = array(
            __DIR__.'/../../../', // default
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
     * Retrieve an iterator for options.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->options);
    }

    /**
     * Return every options.
     *
     * @return array
     */
    public function all()
    {
        return $this->options;
    }

    /**
     * Return an option.
     *
     * @param $name
     * @param null $default
     * @return null|mixed
     */
    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * Check that an option exists.
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->options) && null !== $this->options[$name] ;
    }

    /**
     * Set an option.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->options[$name] = $value;
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

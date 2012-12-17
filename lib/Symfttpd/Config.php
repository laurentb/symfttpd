<?php
/**
 * This file is part of the Symfttpd Server
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd;

/**
 * ServerConfiguration handles the configuration of a server.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Config implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $config = array();

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * Retrieve an iterator for entries.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->config);
    }

    /**
     * Add new entry in the config.
     * Do not use array_merge as it rewrites the keys.
     *
     * @param array $config
     */
    public function add(array $config)
    {
        if (false == empty($config)) {
            $this->config = $this->config + $config;
        }
    }

    /**
     * Return every entries.
     *
     * @return array
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * Clear the config.
     */
    public function clear()
    {
        $this->config = array();
    }

    /**
     * Return an entry.
     *
     * @param $name
     * @param  null       $default
     * @return null|mixed
     */
    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->config[$name];
        }

        return $default;
    }

    /**
     * Check that an entry exists.
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->config) && false === empty($this->config[$name]) ;
    }

    /**
     * Merge config with existing one.
     *
     * @param array $config
     */
    public function merge(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Remove an entry of the config.
     *
     * @param $name
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->config[$name]);
        }
    }

    /**
     * Set an entry.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->config[$name] = $value;
    }
}

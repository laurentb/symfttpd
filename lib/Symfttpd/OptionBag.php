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

namespace Symfttpd;

/**
 * OptionBag class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class OptionBag implements \IteratorAggregate
{
    /**
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->options = $options;
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
     * Add new options with existing ones.
     * Do not use array_merge as it rewrites the keys.
     *
     * @param array $options
     */
    public function add(array $options)
    {
        if (false == empty($options)) {
            $this->options = $this->options + $options;
        }
    }

    /**
     * Merge options with existings ones.
     *
     * @param array $options
     */
    public function merge(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Remove every options.
     */
    public function clear()
    {
        $this->options = array();
    }
}

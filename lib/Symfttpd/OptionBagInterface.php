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
 * OptionBagInterface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface OptionBagInterface extends \IteratorAggregate
{
    /**
     * Return every options.
     *
     * @return array
     */
    public function all();

    /**
     * Return an option.
     *
     * @param $name
     * @param null $default
     * @return null|mixed
     */
    public function get($name, $default = null);

    /**
     * Check that an option exists.
     *
     * @param $name
     * @return bool
     */
    public function has($name);

    /**
     * Set an option.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value);

    /**
     * Add new options with existing ones.
     * Do not use array_merge as it rewrites the keys.
     *
     * @param array $options
     */
    public function add(array $options);

    /**
     * Merge options with existings ones.
     *
     * @param array $options
     */
    public function merge(array $options);

    /**
     * Remove every options.
     */
    public function clear();
}

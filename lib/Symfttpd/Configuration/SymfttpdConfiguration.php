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
use Symfttpd\Exception\ExecutableNotFoundException;

/**
 * SymfttpdConfiguration class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpdConfiguration extends OptionBag
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
                    $this->options = array_merge($this->options, $options);
                    unset($options);
                }
            }
        }
    }
}

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

use Symfttpd\OptionBag;
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

    /**
     * Return the type of the project.
     * If the project is a Symfony2 one, it will return Symfony.
     * This value is set in the configuration file.
     *
     * @return string
     */
    public function getProjectType()
    {
        // BC with the 1.1 configuration version
        if (true == $this->has('want')
            && false == $this->has('project_type')) {
            return "symfony";
        }

        if (false == $this->has('project_type')) {
            throw new \RuntimeException('A project type must be set in the symfttpd.conf.php file.');
        }

        return $this->get('project_type');
    }

    /**
     * Return the project version.
     * For a symfony project it can be 1.4 or 2.0 (which
     * is the same as 2), even 2.1.
     *
     * @return mixed|null
     */
    public function getProjectVersion()
    {
        // Simple PHP project don't need a version.
        if ($this->getProjectType() === 'php') {
            return null;
        }

        // BC with the 1.0 configuration version
        if (true == $this->has('want')
            && false == $this->has('project_version')) {
            return $this->get('want');
        }

        if (false == $this->has('project_version')) {
            throw new \RuntimeException('A project version must be set in the symfttpd.conf.php file.');
        }

        return $this->get('project_version');
    }


    /**
     * Return the type of the server.
     *
     * @return mixed|null|string
     */
    public function getServerType()
    {
        // BC with 1.0 version
        if (true == $this->has('lighttpd_cmd')
            && false == $this->has('server_type')) {
            return 'lighttpd';
        }

        return $this->get('server_type', 'lighttpd');
    }

    /**
     * Return the options for the project.
     *
     * @return array
     */
    public function getProjectOptions()
    {
        $options = array();

        foreach (\Symfttpd\Project\BaseProject::$configurationKeys as $key) {
            $options[$key] = $this->get($key, null);
        }

        return $options;
    }
}

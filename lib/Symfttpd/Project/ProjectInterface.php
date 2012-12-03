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

namespace Symfttpd\Project;

/**
 * ProjectInterface interface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface ProjectInterface
{
    /**
     * @param \Symfttpd\Config $options
     * @param null             $path
     */
    public function __construct(\Symfttpd\Config $options, $path = null);

    /**
     * Return the project name.
     *
     * @abstract
     * @return string
     */
    public function getName();

    /**
     * Return the project version.
     *
     * @abstract
     * @return string
     */
    public function getVersion();

    /**
     * Return the cache directory of the project.
     *
     * @abstract
     * @return mixed
     */
    public function getCacheDir();

    /**
     * Return the log directory of the project.
     *
     * @abstract
     * @return mixed
     */
    public function getLogDir();

    /**
     * Return the web directory of the project.
     *
     * @abstract
     * @return mixed
     */
    public function getWebDir();

    /**
     * Return the index file.
     *
     * @abstract
     * @return mixed
     */
    public function getIndexFile();

    /**
     * Return the directory where lives the project.
     *
     * @abstract
     * @return mixed
     */
    public function getRootDir();

    /**
     * Set the directory where lives the project.
     *
     * @abstract
     * @param $rootDir
     * @return mixed
     */
    public function setRootDir($rootDir);

    /**
     * Return default executable files.
     *
     * @return array
     */
    public function getDefaultExecutableFiles();

    /**
     * Return default readable files.
     *
     * @return mixed
     */
    public function getDefaultReadableDirs();
}

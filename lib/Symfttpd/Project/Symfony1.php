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
 * Symfony1 class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony1 extends BaseProject
{
    /**
     * Return the project name.
     *
     * @return string
     */
    public function getName()
    {
        return 'symfony';
    }

    /**
     * Return the project version.
     *
     * @return string
     */
    public function getVersion()
    {
        return '1';
    }

    /**
     * Return the cache directory of the project.
     *
     * @return mixed
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/cache';
    }

    /**
     * Return the log directory of the project.
     *
     * @return mixed
     */
    public function getLogDir()
    {
        return $this->rootDir.'/log';
    }

    /**
     * Return the web directory of the project.
     *
     * @return mixed
     */
    public function getWebDir()
    {
        return $this->rootDir.'/web';
    }

    /**
     * Return the index file.
     *
     * @return mixed
     */
    public function getIndexFile()
    {
        return 'index.php';
    }
}

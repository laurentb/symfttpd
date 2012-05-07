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

namespace Symfttpd\Tests\Fixtures;

/**
 * TestProject class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class TestProject extends \Symfttpd\Project\BaseProject
{
    protected $name = 'test',
              $version = "1.0";

    protected $rootDir;
    /**
     * Return the cache directory of the project.
     *
     * @return mixed
     */
    public function getCacheDir()
    {
        return $this->getRootDir().'/cache';
    }

    /**
     * Return the log directory of the project.
     *
     * @return mixed
     */
    public function getLogDir()
    {
        return $this->getRootDir().'/log';
    }

    /**
     * Return the web directory of the project.
     *
     * @return mixed
     */
    public function getWebDir()
    {
        return $this->getRootDir().'/web';
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

    /**
     * Return the directory where lives the project.
     *
     * @return mixed
     */
    public function getRootDir()
    {
        if (null == $this->rootDir) {
            $this->rootDir = sys_get_temp_dir().'/symfttpd-project-test';
        }

        return $this->rootDir;
    }

    /**
     * Set the directory where lives the project.
     *
     * @param $rootDir
     * @return mixed
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

}

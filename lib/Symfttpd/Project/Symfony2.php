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

use Symfttpd\Project\BaseProject;

/**
 * Symfony2 class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony2 extends BaseProject
{
    /**
     * The name of the project framework.
     *
     * @var string
     */
    protected $name = 'symfony';

    /**
     * The version of the project framework.
     *
     * @var string
     */
    protected $version = '2';

    /**
     * Return the cache directory of the project.
     *
     * @return mixed
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/app/cache';
    }

    /**
     * Return the log directory of the project.
     *
     * @return mixed
     */
    public function getLogDir()
    {
        return $this->rootDir.'/app/logs';
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
        return 'app.php';
    }
}

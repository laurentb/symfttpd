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
 * Php project represent a simple php project.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Php extends BaseProject
{
    protected $name = 'php',
              $version = null;

    /**
     * Return the cache directory of the project.
     *
     * @return mixed
     */
    public function getCacheDir()
    {
        if ($this->config->has('project_cache_dir')) {
            return $this->rootDir.'/'.$this->config->get('project_cache_dir');
        }

        return $this->rootDir;
    }

    /**
     * Return the log directory of the project.
     *
     * @return mixed
     */
    public function getLogDir()
    {
        if ($this->config->has('project_log_dir')) {
            return $this->rootDir.'/'.$this->config->get('project_log_dir');
        }

        return $this->rootDir;
    }

    /**
     * Return the web directory of the project.
     *
     * @return mixed
     */
    public function getWebDir()
    {
        if ($this->config->has('project_web_dir')) {
            return $this->rootDir.'/'.$this->config->get('project_web_dir');
        }

        return $this->rootDir;
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
     * PHP project does not need a version.
     *
     * @return null
     */
    public function getVersion()
    {
        return $this->version;
    }
}

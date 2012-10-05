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
use Symfttpd\Configuration\ConfigurationInterface;

/**
 * ProjectConfiguration description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ProjectConfiguration extends OptionBag implements ConfigurationInterface
{
    /**
     * @var array Keys of the option set of a project.
     */
    protected $keys = array(
        'project_readable_dirs',     // readable directories by the server in the web dir.
        'project_readable_files',    // readable files by the server in the web dir (robots.txt).
        'project_readable_phpfiles', // executable php files in the web directory (index.php)
        'project_readable_restrict', // true if no other php files are readable than configured ones or index file.
        'project_nophp',             // deny PHP execution in the specified directories (default being uploads).
        'project_log_dir',
        'project_cache_dir',
        'project_web_dir',
    );

    /**
     * Return the available options of a project configuration.
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Return the version of the project.
     *
     * @return mixed|null|string
     * @throws \RuntimeException
     */
    public function getVersion()
    {
        // Simple PHP project don't need a version.
        if ($this->getType() === 'php') {
            return null;
        }

        // BC with the 1.0 configuration version
        if (true == $this->has('want')
            && false == $this->has('project_version')) {
            return substr($this->get('want'), 0, 1);
        }

        if (false == $this->has('project_version')) {
            throw new \RuntimeException('A project version must be set in the symfttpd.conf.php file.');
        }

        return $this->get('project_version');
    }

    /**
     * Return the type of the project.
     *
     * @return mixed|null|string
     * @throws \RuntimeException
     */
    public function getType()
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
}

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

use Symfttpd\Project\Exception\ProjectException;

/**
 * BaseProject class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
abstract class BaseProject implements ProjectInterface
{
    protected $name;

    /**
     * The version of the project framework.
     *
     * @var string
     */
    protected $version;

    /**
     * Directory contained by the web dir, accessible
     * by the web user.
     *
     * @var Array
     */
    public $readableDirs = array();

    /**
     * Files contained by the web dir, accessible
     * by the web user.
     *
     * @var Array
     */
    public $readableFiles = array();

    /**
     * Php executable for the application.
     *
     * @var Array
     */
    public $readablePhpFiles = array();

    /**
     * @var String
     */
    protected $rootDir;

    /**
     * @var \Symfttpd\Config
     */
    public $config;

    public function __construct(\Symfttpd\Config $config, $path = null)
    {
        $this->rootDir = $path;
        $this->config  = $config;
    }

    /**
     * Scan readable files, dirs and php executable files
     * as index.php.
     */
    public function scan()
    {
        // Reset the default values.
        $this->readableDirs     = $this->config->get('project_readable_dirs', array());
        $this->readableFiles    = $this->config->get('project_readable_files', array());
        $this->readablePhpFiles = $this->config->get('project_readable_phpfiles', array('index.php'));

        $iterator = new \DirectoryIterator($this->getWebDir());

        foreach ($iterator as $file) {
            $name = $file->getFilename();
            if ($name[0] != '.') {
                if ($file->isDir() && false == in_array($name, $this->readableDirs)) {
                    $this->readableDirs[] = $name;
                } elseif (!preg_match('/\.php$/', $name) && false == in_array($name, $this->readableFiles)) {
                    $this->readableFiles[] = $name;
                } else {
                    if (false === $this->config->has('project_readable_restrict')
                        && false == in_array($name, $this->readablePhpFiles)
                    ) {
                        $this->readablePhpFiles[] = $name;
                    }
                }
            }
        }

        sort($this->readableDirs);
        sort($this->readableFiles);
        sort($this->readablePhpFiles);
    }

    /**
     * Return the directory where lives the project.
     *
     * @return mixed
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * Set the directory where lives the project.
     *
     * @param $rootDir
     *
     * @throws \InvalidArgumentException
     */
    public function setRootDir($rootDir)
    {
        $realDir = realpath($rootDir);

        if (false == $realDir) {
            throw new \InvalidArgumentException(sprintf('The path "%s" does not exist', $rootDir));
        }

        $this->rootDir = $realDir;
    }

    /**
     * Return the name of the project.
     *
     * @return string
     * @throws \Symfttpd\Project\Exception\ProjectException
     */
    public function getName()
    {
        if (null == $this->name) {
            throw new ProjectException('The name must be set.');
        }

        return $this->name;
    }

    /**
     * Return the version of the project.
     *
     * @return string
     * @throws Exception\ProjectException
     */
    public function getVersion()
    {
        if (null == $this->version) {
            throw new ProjectException('The version must be set.');
        }

        return $this->version;
    }
}

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
    /**
     * The name of the project framework.
     *
     * @var string
     */
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
     * Php executable for symfony applications.
     *
     * @var Array
     */
    public $readablePhpFiles = array();

    /**
     * @var String
     */
    protected $rootDir;

    /**
     * Initialize readable files, dirs and php executable files
     * as index.php.
     */
    public function initialize()
    {
        $this->readableDirs = array();
        $this->readableFiles = array();
        $this->readablePhpFiles = array();

        $iterator = new \DirectoryIterator($this->getWebDir());

        foreach ($iterator as $file) {
            $name = $file->getFilename();
            if ($name[0] != '.') {
                if ($file->isDir()) {
                    $this->readableDirs[] = $name;
                } elseif (!preg_match('/\.php$/', $name)) {
                    $this->readableFiles[] = $name;
                } else {
                    $this->readablePhpFiles[] = $name;
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
     * @throws \InvalidArgumentException
     */
    public function setRootDir($rootDir)
    {
        $rootDir = realpath($rootDir);

        if (false == $rootDir) {
            throw new \InvalidArgumentException(sprintf('The path "%s"does not exist', $rootDir));
        }

        $this->rootDir = $rootDir;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (null == $this->name) {
            throw new ProjectException('The name must be set.');
        }

        return $this->name;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        if (null == $this->name) {
            throw new ProjectException('The version must be set.');
        }

        return $this->version;
    }
}

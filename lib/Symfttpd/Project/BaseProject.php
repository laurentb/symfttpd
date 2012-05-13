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
    static public $configurationKeys = array(
        'project_readable_dirs',     // readable directories by the server in the web dir.
        'project_readable_files',    // readable files by the server in the web dir (robots.txt).
        'project_readable_phpfiles', // executable php files in the web directory (index.php)
        'project_readable_restrict', // true if no other php files are readable than configured ones or index file.
    );

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
     * @var \Symfttpd\Configuration\OptionBag
     */
    protected $options;

    public function __construct(\Symfttpd\Configuration\OptionBag $options, $path = null)
    {
        $this->rootDir = $path;

        $this->readableDirs = $options->get('project_readable_dirs', array());
        $this->readableFiles = $options->get('project_readable_files', array());
        $this->readablePhpFiles = $options->get('project_readable_phpfiles', array('index.php'));

        $this->options = $options;
    }

    /**
     * Initialize readable files, dirs and php executable files
     * as index.php.
     */
    public function initialize()
    {
        $iterator = new \DirectoryIterator($this->getWebDir());

        foreach ($iterator as $file) {
            $name = $file->getFilename();
            if ($name[0] != '.') {
                if ($file->isDir()) {
                    $this->readableDirs[] = $name;
                } elseif (!preg_match('/\.php$/', $name)) {
                    $this->readableFiles[] = $name;
                } else {
                    if (false === $this->options->has('project_readable_restrict')
                        && false == in_array($name, $this->readablePhpFiles)) {
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

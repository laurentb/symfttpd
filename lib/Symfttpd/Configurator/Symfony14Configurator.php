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

namespace Symfttpd\Configurator;

use Symfttpd\Configurator\ConfiguratorInterface;
use Symfttpd\Configurator\Exception\ConfiguratorException;
use Symfttpd\Filesystem\Filesystem;
use Symfony\Component\Process\PhpProcess;

/**
 * Symfony14Configurator class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */
class Symfony14Configurator implements ConfiguratorInterface
{
    /**
     * @var \Symfttpd\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param null|\Symfttpd\Filesystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * Configure the project.
     * Creates cache and log directories, add symbolic links for
     * plugins web assets.
     *
     * @param $path
     * @param array $options
     * @throws \RuntimeException
     * @throws ConfiguratorException
     */
    public function configure($path, array $options)
    {
        // Try to find the symfony executable file.
        if (false == file_exists($path.'/symfony') || false == is_executable($path.'/symfony')) {
            throw new ConfiguratorException('This is not a symfony project.');
        }

        // Creates cache and log folders
        $this->filesystem->mkdir(array($path . '/cache', $path . '/log'));

        $symlinks = array();
        $sfPath = $options['sf_path'][$options['want']];
        $sfSymlinks = array(
            'symfony_symlink' => '',
            'lib_symlink'     => 'lib',
            'data_symlink'    => 'data',
            'web_symlink'     => 'data/web/sf',
        );

        // Add symfony's symlink to the $symlinks array
        foreach ($sfSymlinks as $option => $relpath) {
            $link = $options[$option];
            if ($link) {
                $target = $sfPath . '/' . $relpath;
                if (file_exists($target) && is_dir($target)) {
                    $symlinks[$link] = $target;
                }
            }
        }

        foreach ($symlinks as $link => $target)
        {
            $this->replaceSymlink($path, $target, $link, $options['relative']);
        }

        // Generates
        if ($options['do_plugins']) {
            if (version_compare($options['want'], '1.2') >= 0) {
                $process = new PhpProcess('symfony plugin:publish-assets', realpath($path));
                $process->setTimeout(5);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new \RuntimeException($process->getErrorOutput());
                }
            } else {
                foreach ($this->findPlugins($path) as $name) {
                    $link = 'web/' . $name;
                    $target = $path . '/plugins/' . $name . '/web';
                    // Ignore if there is a real directory with this name
                    if (is_link($path . '/' . $link) || !is_dir($path . '/' . $link)) {
                        $this->replaceSymlink($path, $target, $link, $options['relative']);
                    }
                }
            }
        }
    }

    /**
     * Replace the existing symbolic links.
     *
     * @param $projectPath
     * @param $target
     * @param $link
     * @param bool $relative
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function replaceSymlink($projectPath, $target, $link, $relative = true)
    {
        if ($relative) {
            $target = $this->filesystem->calculateRelativeDir($projectPath . '/' . $link, $target);
        }

        $this->filesystem->mkdir(dirname($projectPath . '/' . $link));
        $this->filesystem->symlink($target, $projectPath . '/' . $link);
    }

    /**
     * Find plugins with a "web" directory
     *
     * @param string $projectPath
     * @return array Plugin names
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function findPlugins($projectPath)
    {
        $plugins = array();
        foreach (new \DirectoryIterator($projectPath . "/plugins") as $file)
        {
            $name = $file->getFilename();
            if ($file->isDir()
                && preg_match('/^[^\.].+Plugin$/', $name)
                && is_dir($projectPath . '/plugins/' . $name . '/web')
            ) {
                $plugins[] = $name;
            }
        }

        return $plugins;
    }
}

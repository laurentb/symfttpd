<?php
/**
 * SymfonyMaker class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Configurator;

use Symfttpd\Configurator\ConfiguratorInterface;
use Symfttpd\Filesystem\Filesystem;
use Symfttpd\FileTools;
use Symfttpd\PosixTools;
use Symfony\Component\Process\Process;

class Symfony14Configurator implements ConfiguratorInterface
{
    protected $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    /**
     * Configure the project.
     * Creates cache and log directories, add symbolic links for
     * plugins web assets.
     *
     * @throws \Exception
     * @param string $path
     * @param array $options
     * @return void
     */
    public function configure($path, array $options)
    {
        // Creates cache and log folders
        $this->filesystem->mkdir(array($path . '/cache', $path . '/log'));

        // Creates symlinks to lighttpd.conf
        $symlinks = array();
        if ($options['genconf']) {
            $symlinks[$options['genconf']] = $options['path'] . '/genconf';
        }

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
                if (empty($options['php_cmd'])) {
                    $options['php_cmd'] = PosixTools::which('php');
                }

                $process = new Process(trim($options['php_cmd'] . ' symfony plugin:publish-assets'), realpath($path));
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
     * @pram string $projectPath Absolute project path
     * @param string $target The destination of the symlink
     * @param string $link The relative path of the symlink to create
     * @param boolean $relative Try to use a relative destination
     * @return boolean Success
     *
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
     * @param string $projectPath
     * @return array Plugin names
     *
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

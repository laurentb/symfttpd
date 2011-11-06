<?php
/**
 * ConfigurationFinder class.
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */

namespace Symfttpd\Finder;

class ConfigurationFinder
{
    protected $paths;

    public function __construct()
    {
        $this->paths = array(
            __DIR__.'/../../../', // default
            getenv('HOME').'/.',  // user configuration
            getcwd().'/config/',  // project configuration
        );
    }

    /**
     * Finds configuration files and returns options.
     *
     * @param string $filename
     * @return array
     */
    public function find($filename = 'symfttpd.conf.php')
    {
        $options = array();

        foreach ($this->paths as $path) {
            $config = $path.'/'.$filename;

            if (file_exists($config)) {
                require $config;
            }
        }

        return $options;
    }

    /**
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
    }

    /**
     * @param array $paths
     * @return void
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }
}

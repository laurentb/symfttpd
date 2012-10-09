<?php
/**
 * This file is part of the Symfttpd Server
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd;

/**
 * ServerConfiguration handles the configuration of a server.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Config implements \IteratorAggregate
{
    /**
     * @var array Keys of the option set of a Server.
     */
    protected $serverKeys = array(
        'php_cgi_cmd',
        'server_pidfile',     // The pidfile stores the PID of the server process.
        'server_restartfile', // The file that tells the spawn command to restart the server.
        'server_access_log',  // The server access log file of the server.
        'server_error_log',   // The server error log file of the server.
    );

    /**
     * @var array Keys of the option set of a project.
     */
    protected $projectKeys = array(
        'project_type',
        'project_version',
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
     * @var array
     */
    protected $config = array();

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        $this->config = $config;
    }

    /**
     * Retrieve an iterator for entries.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->config);
    }

    /**
     * Add new entry in the config.
     * Do not use array_merge as it rewrites the keys.
     *
     * @param array $config
     */
    public function add(array $config)
    {
        if (false == empty($config)) {
            $this->config = $this->config + $config;
        }
    }

    /**
     * Return every entries.
     *
     * @return array
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * Clear the config.
     */
    public function clear()
    {
        $this->config = array();
    }

    /**
     * Return an entry.
     *
     * @param $name
     * @param null $default
     * @return null|mixed
     */
    public function get($name, $default = null)
    {
        if ($this->has($name)) {
            return $this->config[$name];
        }

        return $default;
    }

    /**
     * Check that an entry exists.
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->config) && false === empty($this->config[$name]) ;
    }

    /**
     * Merge config with existing one.
     *
     * @param array $config
     */
    public function merge(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Remove an entry of the config.
     *
     * @param $name
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            unset($this->config[$name]);
        }
    }

    /**
     * Set an entry.
     *
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->config[$name] = $value;
    }
}

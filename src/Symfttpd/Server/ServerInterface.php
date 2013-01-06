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

namespace Symfttpd\Server;

use Symfttpd\Config;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\ProcessAwareInterface;
use Symfttpd\Project\ProjectInterface;

/**
 * ServerInterface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface ServerInterface extends ProcessAwareInterface
{
    /**
     * Run the server command to start it.
     *
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function start(ConfigurationGenerator $generator);

    /**
     * Stop the server.
     *
     * @return mixed
     */
    public function stop();

    /**
     * Restart the server command to start it.
     *
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return mixed
     */
    public function restart(ConfigurationGenerator $generator);

    /**
     * Configure the server.
     *
     * @param \Symfttpd\Config                   $config
     * @param \Symfttpd\Project\ProjectInterface $project
     */
    public function configure(Config $config, ProjectInterface $project);

    /**
     * @param      $address
     * @param null $port
     */
    public function bind($address, $port = null);

    /**
     * Return the type of the server, e.g. lighttpd or nginx.
     *
     * @return mixed
     */
    public function getType();

    /**
     * Return the bounded address of the server e.g. 127.0.0.1.
     *
     * @return string
     */
    public function getAddress();

    /**
     * Return the bounded port of the server.
     *
     * @return string
     */
    public function getPort();

    /**
     * Return the executable used to run the server, e.g. /usr/bin/lighttpd.
     *
     * @return string
     */
    public function getExecutable();

    /**
     * Return the document root of the application.
     *
     * @return string
     */
    public function getDocumentRoot();

    /**
     * Return the index file of the application.
     *
     * @return string
     */
    public function getIndexFile();

    /**
     * Return the pidfile of the server.
     *
     * @return string
     */
    public function getPidfile();

    /**
     * Return the access log file used by the server.
     *
     * @return string
     */
    public function getAccessLog();

    /**
     * Return the error log file used by the server.
     *
     * @return string
     */
    public function getErrorLog();

    /**
     * Return the list of files that the server can execute e.g. app.php.
     *
     * @return array
     */
    public function getExecutableFiles();

    /**
     * Return the list of directories that the server allow access e.g. uploads.
     *
     * @return array
     */
    public function getAllowedDirs();

    /**
     * Return the list that the server can read e.g. favicon.ico.
     *
     * @return array
     */
    public function getAllowedFiles();

    /**
     * Return the list of directories where files should not be executed e.g. uploads.
     *
     * @return array
     */
    public function getUnexecutableDirs();

    /**
     * Return the gateway instance used by the server e.g. php-fpm, fastcgi.
     *
     * @return \Symfttpd\Gateway\GatewayInterface
     */
    public function getGateway();
}

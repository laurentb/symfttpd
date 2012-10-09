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
use Symfttpd\Loader;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Writer;

/**
 * ServerInterface interface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @todo Complete this interface.
 */
interface ServerInterface
{
    /**
     * Server constructor.
     *
     * @param \Symfttpd\Project\ProjectInterface $project
     * @param \Twig_Environment                  $twig
     * @param \Symfttpd\Loader                   $loader
     * @param \Symfttpd\Writer                   $writer
     * @param \Symfttpd\Config                   $config
     */
    public function __construct(ProjectInterface $project, \Twig_Environment $twig, Loader $loader, Writer $writer, Config $config);

    /**
     * Return the project.
     *
     * @abstract
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject();

    /**
     * Return the command that will run the server.
     * It is lighttpd for the Lighttpd server for example.
     *
     * @abstract
     * @return mixed
     */
    public function getCommand();

    /**
     * Set the command that runs the server.
     *
     * @abstract
     * @param $command
     * @return mixed
     */
    public function setCommand($command);

    /**
     * Generate the configuration file of the server
     * and the rewrite rules.
     *
     * @abstract
     * @return string
     */
    public function generate();

    /**
     * Generate the rewrite rules.
     *
     * @abstract
     * @return string
     */
    public function generateRules();

    /**
     * Generate the configuration file for the server.
     *
     * @abstract
     * @return string
     */
    public function generateConfiguration();

    /**
     * Write the configuration in the directory.
     *
     * @abstract
     * @return mixed
     */
    public function write();

    /**
     * Write the rewrite rules.
     *
     * @abstract
     * @return mixed
     */
    public function writeRules();

    /**
     * Write the configuration file.
     *
     * @abstract
     * @return mixed
     */
    public function writeConfiguration();

    /**
     * Run the server command to start it.
     *
     * @abstract
     * @return mixed
     */
    public function start();

    /**
     * Return the restart file path.
     *
     * @abstract
     * @return string
     */
    public function getRestartFile();

    /**
     * Return the pidfile which contains
     * the pid of the process of the server.
     *
     * @abstract
     * @return string
     */
    public function getPidfile();

    /**
     * Return the log directory of the server.
     *
     * @abstract
     * @return string
     */
    public function getLogDir();
}

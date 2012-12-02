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

namespace Symfttpd;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Config;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;
use Symfttpd\Server\Configuration\ConfigurationInterface;

/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfttpd
{
    const VERSION = '@package_version@';

    /**
     * @var \Symfttpd\Config
     */
    protected $config;

    /**
     * @var \Symfttpd\Project\ProjectInterface
     */
    protected $project;

    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @var \Symfttpd\Server\Configuration\ConfigurationInterface
     */
    protected $serverConfiguration;

    /**
     * @param \Symfttpd\Config $config
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return \Symfttpd\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \Symfttpd\Project\ProjectInterface $project
     */
    public function setProject(ProjectInterface $project)
    {
        $this->project = $project;
    }

    /**
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function setServer(ServerInterface $server)
    {
        $this->server = $server;
    }

    /**
     * @return \Symfttpd\Server\ServerInterface
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param \Symfttpd\Server\Configuration\ConfigurationInterface $serverGenerator
     */
    public function setServerConfiguration(ConfigurationInterface $serverGenerator)
    {
        $this->serverConfiguration = $serverGenerator;
    }

    /**
     * @return \Symfttpd\Server\Configuration\ConfigurationInterface
     */
    public function getServerConfiguration()
    {
        return $this->serverConfiguration;
    }

    /**
     * Find executables.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function findExecutables()
    {
        $this->findPhpCmd();
        $this->findPhpCgiCmd();
    }

    /**
     * Set the php command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpCmd()
    {
        if (false === $this->getConfig()->has('php_cmd')) {
            $phpFinder = new PhpExecutableFinder();
            $cmd = $phpFinder->find();

            if (false == (boolean) $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php executable not found');
            }

            $this->getConfig()->set('php_cmd', $cmd);
        }
    }

    /**
     * Set the php-cgi command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpCgiCmd()
    {
        if (false === $this->getConfig()->has('php_cgi_cmd')) {
            $exeFinder = new ExecutableFinder();
            $exeFinder->addSuffix('');
            $cmd = $exeFinder->find('php-cgi');

            if (false == (boolean) $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php-cgi executable not found.');
            }

            $this->getConfig()->set('php_cgi_cmd', $cmd);
        }
    }
}

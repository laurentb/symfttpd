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

use Symfttpd\Tail\TailInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Server\BaseServer;
use Symfttpd\ConfigurationFile\ConfigurationFileInterface;

/**
 * MockServer class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class MockServer extends BaseServer
{
    /**
     * Constructor
     * Set the name of the server.
     */
    public function __construct()
    {
        $this->name = 'mock';
    }

    /**
     * Return the server command value
     *
     * @return string
     */
    public function getCommand()
    {
        return '/foo/bar/mock';
    }

    /**
     * Set the command to use.
     *
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Start the server.
     *
     * @param \Symfttpd\ConfigurationFile\ConfigurationFileInterface $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface     $output
     * @param \Symfttpd\Tail\TailInterface                          $tail
     *
     * @return mixed|void
     */
    public function start(ConfigurationFileInterface $configuration, OutputInterface $output, TailInterface $tail = null)
    {

    }

    /**
     * @param \Symfttpd\ConfigurationFile\ConfigurationFileInterface $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface     $output
     * @param \Symfttpd\Tail\TailInterface                          $tail
     *
     * @return mixed|void
     */
    public function restart(ConfigurationFileInterface $configuration, OutputInterface $output, TailInterface $tail = null)
    {

    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed|void
     */
    public function stop(OutputInterface $output)
    {

    }
}

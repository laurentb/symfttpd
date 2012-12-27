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

namespace Symfttpd\Gateway;

use Symfony\Component\Process\ProcessBuilder;
use Symfttpd\Config;
use Symfttpd\Gateway\GatewayInterface;

/**
 * BaseGateway
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class BaseGateway implements GatewayInterface
{
    /**
     * @var String
     */
    protected $command;

    /**
     * @var string
     */
    protected $socket;

    /**
     * @var \Symfony\Component\Process\ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @param \Symfttpd\Config $config
     *
     * @return \Symfttpd\Config|void
     */
    public function configure(Config $config)
    {
        $this->command = $config->get('gateway_cmd');
    }

    /**
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return String
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param $socket
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    /**
     * @return string
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param \Symfony\Component\Process\ProcessBuilder $pb
     */
    public function setProcessBuilder(ProcessBuilder $pb)
    {
        $this->processBuilder = $pb;
    }

    /**
     * @return \Symfony\Component\Process\ProcessBuilder
     */
    public function getProcessBuilder()
    {
        return $this->processBuilder;
    }
}

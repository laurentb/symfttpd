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

use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\Gateway\GatewayInterface;
use Symfttpd\Server\BaseServer;
use Symfttpd\Server\GatewayAwareInterface;
use Symfttpd\Tail\TailInterface;

/**
 * Nginx description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Nginx extends BaseServer implements GatewayAwareInterface
{
    /**
     * Run the server command to start it.
     *
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\                                   $tail
     *
     * @return mixed
     */
    public function start(ConfigurationGenerator $generator, OutputInterface $output, TailInterface $tail = null)
    {
        // TODO: Implement start() method.
    }

    /**
     * Restart the server command to start it.
     *
     * @param \Symfttpd\ConfigurationGenerator                      $generator
     * @param \Symfony\Component\Console\Output\OutputInterface     $output
     * @param \Symfttpd\Tail\TailInterface                          $tail
     *
     * @return mixed
     */
    public function restart(ConfigurationGenerator $generator, OutputInterface $output, TailInterface $tail = null)
    {
        // TODO: Implement restart() method.
    }

    /**
     * Stop the server.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    public function stop(OutputInterface $output)
    {
        // TODO: Implement stop() method.
    }

    public function setGateway(GatewayInterface $gateway)
    {
        // TODO : implement setGateway() method.
    }

    public function getGateway()
    {
        // TODO : implement getGateway() method.
    }
}
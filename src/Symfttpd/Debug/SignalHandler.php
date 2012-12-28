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

namespace Symfttpd\Debug;

use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Gateway\GatewayProcessableInterface;
use Symfttpd\Server\ServerInterface;

/**
 * SignalHandler description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SignalHandler
{
    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function __construct(ServerInterface $server)
    {
        $this->server = $server;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface                  $server
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    public static function register(ServerInterface $server, OutputInterface $output)
    {
        $handler = new static($server);
        $handler->setOutput($output);

        register_shutdown_function(array($handler, 'shutdown'));

        if (!function_exists('pcntl_signal')) {
            throw \RuntimeException('Symfttpd needs PCNTL to be enabled to handle signals to kill every processes when stopping spawning.');
        }

        pcntl_signal(SIGTERM, array($handler, 'handleSignal'));
        pcntl_signal(SIGINT, array($handler, 'handleSignal'));
    }

    /**
     * Shutdown Symfttpd
     */
    public function shutdown()
    {
        if (null !== $gateway = $this->server->getGateway()) {
            $gateway->stop();
        }

        $this->server->stop();

        if (null != $this->output) {
            $this->output->writeln(PHP_EOL.'<important>Stop serving, bye!</important>');
        }
    }

    /**
     * @param $signo
     */
    public function handleSignal($signo)
    {
        switch ($signo) {
            case SIGTERM:
            case SIGINT:
            default:
                exit(0);
        }
    }
}

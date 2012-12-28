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
use Symfttpd\Log\LoggerInterface;
use Symfttpd\Server\ServerInterface;

/**
 * SignalHandler description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SignalHandler
{
    private $signo = array(
        2  => 'SIGINT',
        15 => 'SIGTERM',
    );

    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var \Symfttpd\Log\LoggerInterface
     */
    protected $logger;

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
     * @param \Symfttpd\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * register the signal handler
     *
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return SignalHandler
     * @throws \RuntimeException
     */
    public static function register(ServerInterface $server)
    {
        $handler = new static($server);

        register_shutdown_function(array($handler, 'shutdown'));

        if (!function_exists('pcntl_signal')) {
            throw \RuntimeException('Symfttpd needs PCNTL to be enabled to handle signals to kill every processes when stopping spawning.');
        }

        pcntl_signal(SIGTERM, array($handler, 'handleSignal'));
        pcntl_signal(SIGINT, array($handler, 'handleSignal'));

        return $handler;
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
        if (null != $this->logger) {
            $this->logger->debug("Signal {$this->signo[$signo]} received");
        }

        switch ($signo) {
            case SIGTERM:
            case SIGINT:
            default:
                exit(0);
        }
    }
}

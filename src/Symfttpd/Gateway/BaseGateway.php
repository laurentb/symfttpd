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
use Symfttpd\ConfigurationGenerator;
use Symfttpd\Gateway\GatewayInterface;
use Symfttpd\Log\LoggerInterface;

/**
 * BaseGateway
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class BaseGateway implements GatewayInterface
{
    /**
     * @var \Symfony\Component\Process\ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var \Symfttpd\Log\LoggerInterface
     */
    protected $logger;

    protected $executable;
    protected $errorLog;
    protected $pidfile;
    protected $socket;
    protected $user;
    protected $group;

    /**
     * @param \Symfttpd\Config $config
     *
     * @return \Symfttpd\Config|void
     */
    public function configure(Config $config)
    {
        $baseDir = $config->get('symfttpd_dir', getcwd().'/symfttpd');

        $this->executable  = $config->get('gateway_cmd', $config->get('php_cgi_cmd'));
        $this->errorLog    = $config->get('gateway_error_log', "$baseDir/log/{$this->getType()}-error.log");
        $this->pidfile     = $config->get('gateway_pidfile', "$baseDir/symfttpd-{$this->getType()}.pid");
        $this->socket      = $config->get('gateway_socket', "$baseDir/symfttpd-{$this->getType()}.sock");

        $group = posix_getgrgid(posix_getgid());
        $this->group = $group['name'];
        $this->user  = get_current_user();
    }

    /**
     * @param $command
     */
    public function setExecutable($command)
    {
        $this->executable = $command;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * {@inheritdoc}
     */
    public function getPidfile()
    {
        return $this->pidfile;
    }

    /**
     * {@inheritdoc}
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @return string
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * Return the name of the user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Return the name of the user's group
     *
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessBuilder(ProcessBuilder $pb)
    {
        $this->processBuilder = $pb;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessBuilder()
    {
        return $this->processBuilder;
    }

    /**
     * @param \Symfttpd\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function start(ConfigurationGenerator $generator)
    {
        // Create the socket file first.
        touch($this->getSocket());

        $process = $this->getProcessBuilder()
            ->setArguments($this->getCommandLineArguments($generator))
            ->getProcess();

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException($process->getErrorOutput());
        }

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getType()} started.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        \Symfttpd\Utils\PosixTools::killPid($this->getPidfile());

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getType()} stopped.");
        }
    }

    /**
     * Return the parts of the command line to run the process.
     *
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return array
     */
    abstract protected function getCommandLineArguments(ConfigurationGenerator $generator);
}

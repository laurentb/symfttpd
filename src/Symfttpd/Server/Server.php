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

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\ProcessBuilder;
use Symfttpd\Config;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\Gateway\GatewayInterface;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;

/**
 * Server class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Server implements ServerInterface
{
    /**
     * Defines supported types of server.
     */
    const TYPE_LIGHTTPD = 'lighttpd';
    const TYPE_NGINX    = 'nginx';

    /**
     * @var \Symfttpd\Gateway\GatewayInterface
     */
    protected $gateway;

    /**
     * @var \Symfony\Component\Process\ProcessBuilder
     */
    protected $processBuilder;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $type;
    protected $address;
    protected $port;
    protected $executable;
    protected $pidfile;
    protected $errorLog;
    protected $accessLog;
    protected $documentRoot;
    protected $indexFile;
    protected $allowedDirs = array();
    protected $allowedFiles = array();
    protected $executableFiles = array();
    protected $unexecutableDirs = array();
    protected $tempPath;

    /**
     * Configure the server.
     *
     * @param \Symfttpd\Config                   $config
     * @param \Symfttpd\Project\ProjectInterface $project
     *
     * @throws \RuntimeException
     */
    public function configure(Config $config, ProjectInterface $project)
    {
        $baseDir = $config->get('symfttpd_dir', getcwd().'/symfttpd');

        $this->tempPath = $baseDir.'/tmp';

        $this->setType($config->get('server_type'));

        $this->bind($config->get('server_address', '127.0.0.1'), $config->get('server_port', '4042'));

        $this->pidfile = $baseDir . '/' . $config->get('server_pidfile', $this->getType().'.pid');

        // Configure server logging
        $logDir = $config->get('server_log_dir', $baseDir .'/log');
        $this->errorLog  = $logDir . '/' . $config->get('server_error_log', 'error.log');
        $this->accessLog = $logDir . '/' . $config->get('server_access_log', 'access.log');

        // Configure project relative directories and files
        $this->documentRoot     = $project->getWebDir();
        $this->indexFile        = $project->getIndexFile();
        $this->allowedDirs      = $config->get('project_readable_dirs', $project->getDefaultReadableDirs());
        $this->allowedFiles     = $config->get('project_readable_files', $project->getDefaultReadableFiles());
        $this->executableFiles  = $config->get('project_readable_phpfiles', $project->getDefaultExecutableFiles());
        $this->unexecutableDirs = $config->get('project_nophp', array());
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function isSupported($type)
    {
        $ref = new \ReflectionClass($this);

        return in_array($type, $ref->getConstants());
    }

    /**
     * {@inheritdoc}
     */
    public function bind($address, $port = null)
    {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * {@inheritdoc}
     */
    public function start(ConfigurationGenerator $generator)
    {
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
        // Kill the current server process.
        \Symfttpd\Utils\PosixTools::killPid($this->getPidfile());

        if (null !== $this->logger) {
            $this->logger->debug("{$this->getType()} stopped.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function restart(ConfigurationGenerator $generator)
    {
        $this->stop();
        $this->start($generator);
    }

    /**
     * Return the command line executed by the process.
     *
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getCommandLineArguments(ConfigurationGenerator $generator)
    {
        switch ($this->getType()) {
            case self::TYPE_LIGHTTPD:
                $arguments = array($this->getExecutable(), '-f', $generator->dump($this, true));
                break;
            case self::TYPE_NGINX:
                $arguments = array($this->getExecutable(), '-c', $generator->dump($this, true));
                break;
            default:
                throw new \RuntimeException('The type of the server must be provided.');
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of the server.
     *
     * @param string $type
     *
     * @throws \RuntimeException
     */
    public function setType($type)
    {
        if (!$this->isSupported($type)) {
            throw new \RuntimeException(sprintf("The provided type of server (%s) is not supported, only %s or %s are available.", $type, self::TYPE_LIGHTTPD, self::TYPE_NGINX));
        }

        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutable()
    {
        return $this->executable;
    }

    /**
     * @param string $cmd The executable used to run the server e.g. /usr/bin/lighttpd
     */
    public function setExecutable($cmd)
    {
        $this->executable = $cmd;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * {@inheritdoc}
     */
    public function getIndexFile()
    {
        return $this->indexFile;
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
    public function getAccessLog()
    {
        return $this->accessLog;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * {@inheritdoc}
     */
    public function getExecutableFiles()
    {
        return $this->executableFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedDirs()
    {
        return $this->allowedDirs;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedFiles()
    {
        return $this->allowedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function getUnexecutableDirs()
    {
        return $this->unexecutableDirs;
    }

    /**
     * Set the gateway instance used by the server.
     *
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * Set the process builder instance.
     *
     * @param \Symfony\Component\Process\ProcessBuilder $pb
     */
    public function setProcessBuilder(ProcessBuilder $pb)
    {
        $this->processBuilder = $pb;
    }

    /**
     * Return the process builder instance.
     *
     * @return null|\Symfony\Component\Process\ProcessBuilder $pb
     */
    public function getProcessBuilder()
    {
        return $this->processBuilder;
    }

    /**
     * Set the logger instance.
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Return the logger instance.
     *
     * @return null|\Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Return the temporary path
     *
     * @return string
     */
    public function getTempPath()
    {
      return $this->tempPath;
    }
}

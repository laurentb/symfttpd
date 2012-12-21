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
use Symfttpd\Server\ServerInterface;
use Symfttpd\Tail\TailInterface;

/**
 * BaseServer class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class BaseServer implements ServerInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * Server address
     *
     * @var string
     */
    protected $address;

    /**
     * Server port
     *
     * @var string
     */
    protected $port;

    /**
     * The shell command to run lighttpd.
     *
     * @var string
     */
    protected $command;

    /**
     * Return the pidfile of the server.
     * Used to kill the process.
     *
     * @var string
     */
    protected $pidfile;

    /**
     * @var string
     */
    protected $errorLog;

    /**
     * @var string
     */
    protected $accessLog;

    /**
     * @var string
     */
    protected $documentRoot;

    /**
     * @var string
     */
    protected $fastcgi;

    /**
     * @var array
     */
    protected $allowedDirs = array();

    /**
     * @var array
     */
    protected $allowedFiles = array();

    /**
     * @var array
     */
    protected $executableFiles = array();

    /**
     * @var array
     */
    protected $unexecutableDirs = array();

    /**
     * @var string
     */
    protected $indexFile;

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param      $address
     * @param null $port
     */
    public function bind($address, $port = null)
    {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $pidfile
     */
    public function setPidfile($pidfile)
    {
        $this->pidfile = $pidfile;
    }

    /**
     * @return string
     */
    public function getPidfile()
    {
        return $this->pidfile;
    }

    /**
     * @param string $accessLog
     */
    public function setAccessLog($accessLog)
    {
        $this->accessLog = $accessLog;
    }

    /**
     * @return string
     */
    public function getAccessLog()
    {
        return $this->accessLog;
    }

    /**
     * @param array $allowedDirs
     */
    public function setAllowedDirs(array $allowedDirs)
    {
        $this->allowedDirs = $allowedDirs;
    }

    /**
     * @return array
     */
    public function getAllowedDirs()
    {
        return $this->allowedDirs;
    }

    /**
     * @param array $allowedFiles
     */
    public function setAllowedFiles(array $allowedFiles)
    {
        $this->allowedFiles = $allowedFiles;
    }

    /**
     * @return array
     */
    public function getAllowedFiles()
    {
        return $this->allowedFiles;
    }

    /**
     * @param array $unexecutableDirs
     */
    public function setUnexecutableDirs(array $unexecutableDirs)
    {
        $this->unexecutableDirs = $unexecutableDirs;
    }

    /**
     * @return array
     */
    public function getUnexecutableDirs()
    {
        return $this->unexecutableDirs;
    }

    /**
     * @param string $documentRoot
     */
    public function setDocumentRoot($documentRoot)
    {
        $this->documentRoot = $documentRoot;
    }

    /**
     * @return string
     */
    public function getDocumentRoot()
    {
        return $this->documentRoot;
    }

    /**
     * @param string $errorLog
     */
    public function setErrorLog($errorLog)
    {
        $this->errorLog = $errorLog;
    }

    /**
     * @return string
     */
    public function getErrorLog()
    {
        return $this->errorLog;
    }

    /**
     * @param array $executableFiles
     */
    public function setExecutableFiles(array $executableFiles)
    {
        $this->executableFiles = $executableFiles;
    }

    /**
     * @return array
     */
    public function getExecutableFiles()
    {
        return $this->executableFiles;
    }

    /**
     * @param string $fastcgi
     */
    public function setGateway($fastcgi)
    {
        $this->fastcgi = $fastcgi;
    }

    /**
     * @return string
     */
    public function getGateway()
    {
        return $this->fastcgi;
    }

    /**
     * @param string $indexFile
     */
    public function setIndexFile($indexFile)
    {
        $this->indexFile = $indexFile;
    }

    /**
     * @return string
     */
    public function getIndexFile()
    {
        return $this->indexFile;
    }

    /**
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return mixed|void
     */
    public function restart(ConfigurationGenerator $generator, OutputInterface $output, TailInterface $tail = null)
    {
        $this->stop(new \Symfony\Component\Console\Output\NullOutput());
        $this->start($generator, $output, $tail);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed|void
     */
    public function stop(OutputInterface $output)
    {
        // Kill the current server process.
        \Symfttpd\Utils\PosixTools::killPid($this->getPidfile(), $output);

        $output->writeln($this->getName().' stopped');
    }
}

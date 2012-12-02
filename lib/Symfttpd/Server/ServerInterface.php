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
use Symfttpd\Tail\TailInterface;
use Symfttpd\Server\Configuration\ConfigurationInterface;

/**
 * ServerInterface interface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @todo Complete this interface.
 */
interface ServerInterface
{
    /**
     * Run the server command to start it.
     *
     * @abstract
     * @param \Symfttpd\Server\Configuration\ConfigurationInterface $generator
     * @param \Symfony\Component\Console\Output\OutputInterface     $output
     * @param \Symfttpd\Tail\TailInterface                          $tail
     *
     * @return mixed
     */
    public function start(ConfigurationInterface $generator, OutputInterface $output, TailInterface $tail = null);

    /**
     * Restart the server command to start it.
     *
     * @abstract
     * @param \Symfttpd\Server\Configuration\ConfigurationInterface $generator
     * @param \Symfony\Component\Console\Output\OutputInterface     $output
     * @param \Symfttpd\Tail\TailInterface                          $tail
     *
     * @return mixed
     */
    public function restart(ConfigurationInterface $generator, OutputInterface $output, TailInterface $tail = null);

    /**
     * Stop the server.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    public function stop(OutputInterface $output);

    /**
     * @param      $address
     * @param null $port
     *
     * @return mixed
     */
    public function bind($address, $port = null);

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getPort();

    /**
     * @param string $command
     */
    public function setCommand($command);

    /**
     * @return string
     */
    public function getCommand();

    /**
     * @param string $pidfile
     */
    public function setPidfile($pidfile);

    /**
     * @return string
     */
    public function getPidfile();

    /**
     * @param string $accessLog
     */
    public function setAccessLog($accessLog);

    /**
     * @return string
     */
    public function getAccessLog();

    /**
     * @param array $allowedDirs
     */
    public function setAllowedDirs(array $allowedDirs);

    /**
     * @return array
     */
    public function getAllowedDirs();

    /**
     * @param array $allowedFiles
     */
    public function setAllowedFiles(array $allowedFiles);

    /**
     * @return array
     */
    public function getAllowedFiles();

    /**
     * @param array $unexecutableDirs
     */
    public function setUnexecutableDirs(array $unexecutableDirs);

    /**
     * @return array
     */
    public function getUnexecutableDirs();

    /**
     * @param string $documentRoot
     */
    public function setDocumentRoot($documentRoot);

    /**
     * @return string
     */
    public function getDocumentRoot();

    /**
     * @param string $errorLog
     */
    public function setErrorLog($errorLog);

    /**
     * @return string
     */
    public function getErrorLog();

    /**
     * @param array $executableFiles
     */
    public function setExecutableFiles(array $executableFiles);

    /**
     * @return array
     */
    public function getExecutableFiles();

    /**
     * @param string $fastcgi
     */
    public function setFastcgi($fastcgi);

    /**
     * @return string
     */
    public function getFastcgi();

    /**
     * @param string $indexFile
     */
    public function setIndexFile($indexFile);

    /**
     * @return string
     */
    public function getIndexFile();
}

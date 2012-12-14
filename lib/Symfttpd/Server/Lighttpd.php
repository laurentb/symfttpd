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
 * Lighttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Lighttpd extends BaseServer
{
    /**
     * Constructor
     * Set the name of the server.
     */
    public function __construct()
    {
        $this->name = 'lighttpd';
    }

    /**
     * Return the server command value
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
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
     * @param \Symfttpd\ConfigurationFile\ConfigurationFileInterface $configuration
     * @param \Symfony\Component\Console\Output\OutputInterface     $output
     * @param \Symfttpd\Tail\TailInterface                          $tail
     *
     * @return mixed|void
     */
    public function start(ConfigurationFileInterface $configuration, OutputInterface $output, TailInterface $tail = null)
    {
        // Regenerate the lighttpd configuration
        $configuration->dump($this, true);

        $process = new \Symfony\Component\Process\Process(null);
        $process->setCommandLine($this->getCommand() . ' -f ' . escapeshellarg($configuration->getPath()));
        $process->setTimeout(null);

        // Run lighttpd
        $process->run();

        $stderr = $process->getErrorOutput();

        if (!empty($stderr)) {
            throw new \RuntimeException($stderr);
        }

        $prevGenconf = null;
        while (false !== sleep(1)) {
            /**
             * Regenerate the configuration file. to check if it defers.
             * @todo check the web dir datetime informations to detect any changes instead.
             */
            $genconf = $configuration->generate($this);

            if ($prevGenconf !== null && $prevGenconf !== $genconf) {
                // This sleep() is so that if a HTTP request just created a file in web/,
                // the web server isn't restarted right away.
                sleep(1);

                $output->writeln(sprintf('<comment>Something in web/ changed. Restarting %s.</comment>', $this->name));

                return $this->restart($configuration, $output, $tail);
            }
            $prevGenconf = $genconf;

            if ($tail instanceof TailInterface) {
                $tail->consume();
            }
        }
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
        $this->stop(new NullOutput());
        $this->start($configuration, $output, $tail);
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
    }
}

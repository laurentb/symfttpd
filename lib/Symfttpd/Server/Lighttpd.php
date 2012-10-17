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

use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Tail\TailInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfttpd\Server\BaseServer;
use Symfttpd\Server\Generator\GeneratorInterface;

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
     * @param null|\Symfony\Component\Process\ExecutableFinder $finder
     *
     * @return string
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function getCommand(ExecutableFinder $finder = null)
    {
        if (null == $this->command) {

            if (null == $finder) {
                $finder = new ExecutableFinder();
            }

            $finder->addSuffix('');
            $cmd = $finder->find('lighttpd');

            if (null == $cmd) {
                throw new ExecutableNotFoundException('lighttpd executable not found.');
            }

            $this->command = $cmd;
        }

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
     * @param \Symfttpd\Server\Generator\GeneratorInterface     $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return mixed|void
     */
    public function start(GeneratorInterface $generator, OutputInterface $output, TailInterface $tail = null)
    {
        // Run lighttpd
        try {
            // Regenerate the lighttpd configuration
            $generator->dump($this, true);

            $process = new \Symfony\Component\Process\Process(null);
            $process->setCommandLine($this->getCommand() . ' -f ' . escapeshellarg($generator->getPath()));
            $process->setTimeout(null);
            $process->run();
        } catch (\Exception $e) {
            $output->writeln('<error>The server cannot start</error>');
            $output->writeln(sprintf('<error>%s</error>', trim($e->getMessage(), " \0\t\r\n")));
        }

        $prevGenconf = null;
        while (false !== sleep(1)) {
            /**
             * Regenerate the configuration file. to check if it defers.
             * @todo check the web dir datetime informations to detect any changes instead.
             */
            $genconf = $generator->generate($this);

            if ($prevGenconf !== null && $prevGenconf !== $genconf) {
                // This sleep() is so that if a HTTP request just created a file in web/,
                // the web server isn't restarted right away.
                sleep(1);

                $output->writeln(sprintf('<comment>Something in web/ changed. Restarting %s.</comment>', $this->name));

                return $this->restart($generator, $output, $tail);
            }
            $prevGenconf = $genconf;

            if ($tail instanceof TailInterface) {
                $tail->consume();
            }
        }
    }

    /**
     * @param \Symfttpd\Server\Generator\GeneratorInterface     $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return mixed|void
     */
    public function restart(GeneratorInterface $generator, OutputInterface $output, TailInterface $tail = null)
    {
        $this->stop(new NullOutput());
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
    }
}

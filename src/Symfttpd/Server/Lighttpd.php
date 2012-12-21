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
use Symfttpd\ConfigurationGenerator;

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
     * Start the server.
     *
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return mixed|void
     */
    public function start(ConfigurationGenerator $generator, OutputInterface $output, TailInterface $tail = null)
    {
        $process = new \Symfony\Component\Process\Process(null);
        $process->setCommandLine(implode(' ', array($this->getCommand(), '-f', $generator->dump($this, true))));
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
             *
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
}

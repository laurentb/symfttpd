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
use Symfttpd\Server\BaseServer;
use Symfttpd\Server\GatewayUnawareInterface;
use Symfttpd\Tail\TailInterface;

/**
 * Nginx description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Nginx extends BaseServer implements GatewayUnawareInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'nginx';
    }

    /**
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfttpd\Tail\TailInterface                      $tail
     *
     * @return mixed|void
     * @throws \RuntimeException
     */
    public function start(ConfigurationGenerator $generator, OutputInterface $output, TailInterface $tail = null)
    {
        $generator->dump($this, true);

        $process = new \Symfony\Component\Process\Process(null);
        $process->setCommandLine($this->getCommand() . ' -c ' . escapeshellarg($generator->getPath()));
        $process->setTimeout(null);
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

                $this->restart($generator, $output, $tail);
            }
            $prevGenconf = $genconf;

            if ($tail instanceof TailInterface) {
                $tail->consume();
            }
        }
    }
}

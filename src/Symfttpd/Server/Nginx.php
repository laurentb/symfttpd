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
        touch($this->getPidfile());

        $process = new \Symfony\Component\Process\Process(null);
        $process->setCommandLine(implode(' ', array($this->getCommand(), '-c', $generator->dump($this, true))));
        $process->setTimeout(null);
        $process->run();

        $stderr = $process->getErrorOutput();

        if (!empty($stderr)) {
            throw new \RuntimeException($stderr);
        }

        while (false !== sleep(1)) {
            if ($tail instanceof TailInterface) {
                $tail->consume();
            }
        }
    }
}

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

namespace Symfttpd\Command;

use Symfttpd\Symfttpd;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Command class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Command extends BaseCommand
{
    /**
     * @var \Symfttpd\Symfttpd $symfttpd
     */
    public $symfttpd;

    /**
     * Return the current Symfttpd instance.
     *
     * @return \Symfttpd\Symfttpd
     */
    public function getSymfttpd()
    {
        if (null === $this->symfttpd) {
            throw new \RuntimeException('The command does not know Symfttpd.');
        }

        return $this->symfttpd;
    }

    /**
     * @param \Symfttpd\Symfttpd $symfttpd
     */
    public function setSymfttpd(Symfttpd $symfttpd)
    {
        $this->symfttpd = $symfttpd;
    }

    /**
     * Initialize the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<comment>Symfttpd - version %s</comment>', Symfttpd::VERSION));
    }
}

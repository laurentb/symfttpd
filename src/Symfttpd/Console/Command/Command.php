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

namespace Symfttpd\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfttpd\Symfttpd;

/**
 * Command class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Command extends BaseCommand
{
    /**
     * Initialize the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle('important', new OutputFormatterStyle('yellow', null, array('bold')));

        $output->writeln(sprintf('<comment>Symfttpd - version %s</comment>', Symfttpd::VERSION));
    }
}

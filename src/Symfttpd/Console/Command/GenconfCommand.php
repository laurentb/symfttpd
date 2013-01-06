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

use Symfttpd\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfttpd\Configuration\Exception\ConfigurationException;

/**
 * GenconfCommand class.
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class GenconfCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('genconf')
            ->setDescription('Generates the host configuration file.')
            ->setHelp(<<<EOT
The genconf command generates the host configuration for the server.
EOT
        );

        // Configure options.
        $this->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'Path of the web directory. Autodected to /web if not present.', getcwd())
            ->addOption('output', 'o', InputOption::VALUE_NONE, 'Directly output the generated configuration.')
            ->addOption('port', null, InputOption::VALUE_OPTIONAL, 'The port to listen', 4042)
            ->addOption('bind', null, InputOption::VALUE_OPTIONAL, 'The address to bind', '127.0.0.1');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Don't print the Symfttpd version if output option is set.
        if (null == $input->getOption('output')) {
            parent::initialize($input, $output);
        }
    }

    /**
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->getOption('output')) {
            // Set the output to null to not print any comment.
            $baseOutput = $output;
            $output = new \Symfony\Component\Console\Output\NullOutput();
        }

        $output->writeln('Starting generating symfttpd configuration.');

        $container = $this->getApplication()->getContainer();

        $container['project']->setRootDir($input->getOption('path'));
        $server = $container['server'];
        $server->bind($input->getOption('bind'), $input->getOption('port'));

        try {

            if (null == $input->getOption('output')) {
                $container['generator']->dump($server, true);
            } else {
                $baseOutput->write($container['generator']->generate($server));
            }

        } catch (ConfigurationException $e) {
            $output->writeln('<error>An error occurred while file generation.</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('The configuration file has been well generated.');

        return 0;
    }
}

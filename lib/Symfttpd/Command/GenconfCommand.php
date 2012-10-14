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

use Symfttpd\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfttpd\Symfttpd;
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

        // Configure arguments
        $this->addArgument('type', InputArgument::OPTIONAL, 'The config file type (config, rules, all).', 'rules');

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
        // Don't print the Symfttpd version if ouput option is set.
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
            $output = new \Symfony\Component\Console\Output\NullOutput();
        }

        $output->writeln('Starting generating symfttpd configuration.');

        $this->getSymfttpd()->getProject()->setRootDir($input->getOption('path'));
        $server = $this->getSymfttpd()->getServer();
        $server->options->set('port', $input->getOption('port'));
        $server->options->set('bind', $input->getOption('bind'));

        try {
            switch ($input->getArgument('type')) {
                case 'config':
                    $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getConfigFilename(),
                        $server->getCacheDir()
                    ));
                    $server->generateConfiguration($this->getSymfttpd()->getConfiguration());
                    break;
                case 'rules':
                    $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getRulesFilename(),
                        $server->getCacheDir()
                    ));
                    $server->generateRules();
                    break;
                default:
                    $output->writeln(sprintf(
                        'Generate <comment>%s</comment> and <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getConfigFilename(),
                        $server->getRulesFilename(),
                        $server->getCacheDir()
                    ));
                    $server->generate($this->getSymfttpd()->getConfiguration());
            }

            if (null == $input->getOption('output')) {
                $server->write(true);
            } else {
                switch ($input->getArgument('type')) {
                    case 'config':
                        print $server->readConfiguration();
                        break;
                    case 'rules':
                        print $server->readRules();
                        break;
                    default:
                        print $server->read();
                        break;
                }
            }

        } catch (ConfigurationException $e) {
            $output->writeln('<error>An error occured while file generation.</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('The configuration file has been well generated.');

        return 0;
    }
}

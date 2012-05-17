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
use Symfttpd\Configuration\LighttpdConfiguration;
use Symfttpd\Configuration\SymfttpdConfiguration;
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
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Path of the web directory. Autodected to /web if not present.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting generating symfttpd configuration.');

        $symfttpd = $this->getSymfttpd();

        $project = $symfttpd->getProject();
        $project->setRootDir(getcwd());
        $project->scan();

        $server = $this->getSymfttpd()->getServer();

        if (null !== $input->getOption('path')) {
            $server->options->set('document_root', $input->getOption('path'));
        }

        try {
            switch ($input->getArgument('type'))
            {
                case 'config':
                    $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getConfigFilename(),
                        $server->options->get('cache_dir')
                    ));
                    $server->generateConfiguration($this->getSymfttpd()->getConfiguration());
                    break;
                case 'rules':
                    $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getRulesFilename(),
                        $server->options->get('cache_dir')
                    ));
                    $server->generateRules($this->getSymfttpd()->getConfiguration());
                    break;
                default:
                    $output->writeln(sprintf(
                        'Generate <comment>%s</comment> and <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getConfigFilename(),
                        $server->getRulesFilename(),
                        $server->options->get('cache_dir')
                    ));
                    $server->generate($this->getSymfttpd()->getConfiguration());
            }

            $server->write($input->getArgument('type'), true);
        } catch (ConfigurationException $e) {
            $output->writeln('<error>An error occured while file generation.</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('The configuration file has been well generated.');

        return 0;
    }
}

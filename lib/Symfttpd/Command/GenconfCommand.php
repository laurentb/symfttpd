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
        $this->addArgument('type', InputArgument::OPTIONAL, 'The config file type (config, rules, all).', 'all');

        // Configure options.
        $this->addOption('default',   null, InputOption::VALUE_OPTIONAL, 'Change the default application.', 'index')
            ->addOption('only',       null, InputOption::VALUE_OPTIONAL, 'Do not allow any other application.', false)
            ->addOption('allow',      null, InputOption::VALUE_OPTIONAL, 'Useful with `only`, allow some other applications (useful for allowing a _dev alternative, for example).', false)
            ->addOption('nophp',      null, InputOption::VALUE_OPTIONAL, 'Deny PHP execution in the specified directories (default being uploads).', 'uploads')
            ->addOption('path',       null, InputOption::VALUE_OPTIONAL, 'Path of the web directory. Autodected to ../web if not present.', getcwd() . '/../web')
            ->addOption('output-dir', null, InputOption::VALUE_OPTIONAL, 'The path to generate the configuration.', getcwd().'/cache/lighttpd/');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeVersion($output);
        $output->writeln('Starting generating symfttpd configuration.');

        $server = $this->getSymfttpd()->getServer(getcwd());
        $server->options->add($this->getServerOptions($input->getOptions()));

        try {
            $configDir = $input->getOption('output-dir');

            switch ($input->getArgument('type'))
            {
                case 'config':
                    $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.', $server->getConfigFilename(), $configDir));
                    $server->generateConfiguration($this->getSymfttpd()->getConfiguration());
                    break;
                case 'rules':
                    $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.', $server->getRulesFilename(), $configDir));
                    $server->generateRules($this->getSymfttpd()->getConfiguration());
                    break;
                default:
                    $output->writeln(sprintf(
                        'Generate <comment>%s</comment> and <comment>%s</comment> in <info>"%s"</info>.',
                        $server->getConfigFilename(),
                        $server->getRulesFilename(),
                        $configDir
                    ));
                    $server->generate($this->getSymfttpd()->getConfiguration());
            }

            $server->write($input->getArgument('type'));

        } catch (ConfigurationException $e) {
            $output->writeln('<error>An error occured while file generation.</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('The configuration file has been well generated.');

        return 0;
    }

    /**
     * Create the server options passed to the command.
     *
     * @param array $options
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getServerOptions(array $options)
    {
        $allow = explode(',', $options['allow']);
        $nophp = explode(',', $options['nophp']);
        $path  = realpath($options['path']);

        $files = array(
            'dir' => array(),
            'php' => array(),
            'file' => array()
        );

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('Directory "%s" not found.', $options['path']));
        }

        foreach (new \DirectoryIterator($path) as $file) {
            $name = $file->getFilename();
            if ($name[0] != '.') {
                if ($file->isDir()) {
                    $files['dir'][] = $name;
                }
                elseif (!preg_match('/\.php$/', $name)) {
                    $files['file'][] = $name;
                }
                elseif (empty($options['only'])) {
                    $files['php'][] = $name;
                }
            }
        }

        foreach ($allow as $name) {
            $files['php'][] = $name . '.php';
        }

        return array(
            'document_root' => $path,
            'nophp'    => $nophp,
            'default'  => $options['default'],
            'phps'     => $files['php'],
            'files'    => $files['file'],
            'dirs'     => $files['dir'],
        );
    }
}
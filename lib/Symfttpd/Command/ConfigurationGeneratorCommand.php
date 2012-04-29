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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfttpd\Symfttpd;
use Symfttpd\Configuration\LighttpdConfiguration;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Configuration\Exception\ConfigurationException;

/**
 * ConfigurationGenerator class.
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class ConfigurationGeneratorCommand extends Command
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
        $output->writeln(sprintf('<comment>Symfttpd - version %s</comment>', Symfttpd::VERSION));
        $output->writeln('Starting generating symfttpd configuration.');

        $configuration = new LighttpdConfiguration();

        $symfttpdConfig = $this->getSymfttpdConfiguration($input->getOptions());

        try {
            $configDir = $input->getOption('output-dir');

            $output->writeln(sprintf('Generate <comment>%s</comment> in <info>"%s"</info>.', $configuration->getFilename(), $configDir));

            $configuration->generateHost($symfttpdConfig);

            if (true == $input->getOption('quiet')) {
                print $configuration->readHost();
            } else {
                $configuration->writeHost($configDir);
            }

        } catch (ConfigurationException $e) {
            $output->writeln('<error>An error occured while file generation.</error>');
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return 1;
        }

        $output->writeln('The configuration file has been well generated.');

        return 0;
    }

    /**
     * Create the SymfttpdConfiguration class with the
     * options passed to the command.
     *
     * @param array $options
     * @return \Symfttpd\Configuration\SymfttpdConfiguration
     * @throws \InvalidArgumentException
     */
    public function getSymfttpdConfiguration(array $options)
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

        $symfttpdConfig = new SymfttpdConfiguration(array(
            'document_root' => $path,
            'nophp'   => $nophp,
            'default' => $options['default'],
            'php'     => $files['php'],
            'file'    => $files['file'],
            'dir'     => $files['dir'],
        ));

        return $symfttpdConfig;
    }
}
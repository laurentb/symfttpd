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

use Symfttpd\Validator\ProjectTypeValidator;
use Symfttpd\Configurator\Exception\ConfiguratorNotFoundException;
use Symfttpd\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * MksymlinksCommand class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 24/10/11
 */
class MksymlinksCommand extends Command
{
    protected $finder;

    public function configure()
    {
        $this->setName('mksymlinks')
            ->setDescription('Generates project symbolic links to the web folder');

        // Configure Arguments
        $this->addArgument('type', InputArgument::REQUIRED, 'Type of project you want to setup (symfony for example).');

        // Configure options
        $this->addOption('ver', null, InputOption::VALUE_REQUIRED, 'The version of the project type.', null)
            ->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'The path of the project.', getcwd());
    }

    /**
     * Create symbolic links.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \Symfttpd\Configurator\Exception\ConfiguratorNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $symfttpd = $this->getSymfttpd();

        $project = $symfttpd->getProject();
        $project->setRootDir($input->getOption('path'));
        $project->scan();

        $type    = $input->getArgument('type');
        $version = $input->getOption('ver');

        $output->writeln(sprintf('Generate symbolic links for <info>%s</info> version <info>%s</info>', $type, $version));

        if (!ProjectTypeValidator::getInstance()->isValid($type, $version)) {
            throw new ConfiguratorNotFoundException(sprintf('Symfttpd does not support %s with the version %s yet.', $type, $version));
        }

        $class = 'Symfttpd\\Configurator\\'.ucfirst($type).'Configurator';

        if (!class_exists($class)) {
            throw new ConfiguratorNotFoundException(sprintf('"%s" configurator not found', $type));
        }

        $configurator = new $class($version);
        $configurator->configure($project, $this->getSymfttpd()->getConfiguration()->all());

        $output->writeln('Symbolic links created.');
    }
}

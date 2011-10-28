<?php
/**
 * MksymlinksCommand class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 24/10/11
 */

namespace Symfttpd\Command;

use Symfttpd\FileTools;
use Symfttpd\Color;
use Symfttpd\Argument;
use Symfttpd\MultiConfig;
use Symfttpd\PosixTools;
use Symfttpd\Symfony;
use Symfttpd\Validator\ProjectTypeValidator;
use Symfttpd\Validator\Exception\NotSupportedProjectException;
use Symfttpd\Configurator\Exception\ConfiguratorNotFoundException;

use Symfony\Component\Console\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MksymlinksCommand extends Command
{
    public function configure()
    {
        $this->setName('mksymlinks');
        $this->setDescription('Generates project symbolic links to the web folder');
        $this->addArgument('type', InputArgument::REQUIRED, 'Type of project you want to setup (symfony for example).');
        $this->addOption('ver', null, InputOption::VALUE_OPTIONAL, 'The version of the project type.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type    = $input->getArgument('type');
        $version = $input->getOption('version');

        if (!ProjectTypeValidator::getInstance()->isValid($type, $version)) {
            throw new ConfiguratorNotFoundException(sprintf('Symfttp does not support %s with the version yet.', $type, $version));
        }

        $class = ucfirst($type).'Configurator';

        if (!class_exists($class)) {
            throw new ConfiguratorNotFound(sprintf('"%s" configurator not found', $type));
        }

        $configurator = new $class($version);
        $configurator->configure();
    }
}
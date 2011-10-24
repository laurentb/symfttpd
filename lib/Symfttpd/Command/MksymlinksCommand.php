<?php
/**
 * MksymlinksCommand class.
 * 
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 24/10/11
 */
 
namespace Symfttpd\Command;

require_once __DIR__.'/../bootstrap.php';

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
        $this->setDescription('Generates Symfony 1.x plugins symbolic links to the web folder');
        $this->addArgument('type', InputArgument::REQUIRED, 'Type of project you want to setup.', 'Symfony');
        $this->addOption('version', 'v', InputOption::VALUE_OPTIONAL, 'The version of the project type.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type    = $input->getArgument('type');
        $version = $input->getOption('version');

        if (!ProjectTypeValidator::isValid($type, $version)) {
            throw new ConfiguratorNotFoundException(sprintf('Symfttp does not support %s with the version yet.', $type, $version));
        }

        $class = ucfirst($type).'Configurator';

        if (!class_exists($class)) {
            throw new ConfiguratorNotFound(sprintf('"%s" configurator not found', $type));
        }

        $configurator = new $class($version);

        $options = MultiConfig::get();
        $options['color'] = !Argument::get('C', 'no-color', false) && posix_isatty(STDOUT);
        if ($options['color'])
        {
          Color::enable();
        }

        $project_path = Symfony::getProjectPath();

        log_message(Color::style('bright') . Color::fgColor('green')
          . 'Using symfony version ' . Color::style('normal')
          . Color::fgColor('yellow') . $options['want'] . Color::style('normal'));

        $symlinks = array();
        if ($options['genconf'])
        {
          $symlinks[$options['genconf']] = $options['path'].'/genconf';
        }

        $sf_path = $options['sf_path'][$options['want']];
        foreach (array(
            'symfony_symlink' => '',
            'lib_symlink' => 'lib',
            'data_symlink' => 'data',
            'web_symlink' => 'data/web/sf',
          )
          as $option => $relpath)
        {
          $link = $options[$option];
          if ($link)
          {
            $target = $sf_path.'/'.$relpath;
            if (!is_dir($target))
            {
              throw new \Exception($target.' is not a directory');
            }
            $symlinks[$link] = $target;
          }
        }

        log_message(Color::style('bright') . Color::fgColor('green')
          . 'Creating required directories...' . Color::style('normal'));
        FileTools::mkdirs($project_path.'/cache');
        FileTools::mkdirs($project_path.'/log');

        log_message(Color::style('bright') . Color::fgColor('green')
          . 'Creating symbolic links...' . Color::style('normal'));
        foreach ($symlinks as $link => $target)
        {
          $this->replaceSymlink($project_path, $target, $link, $options['relative']);
        }
        if ($options['do_plugins'])
        {
          if (version_compare($options['want'], '1.2') >= 0)
          {
            log_message(Color::style('bright') . Color::fgColor('green')
              . 'Creating symbolic links for plugins... (calling symfony)'
              . Color::style('normal'));
            if (empty($options['php_cmd']))
            {
              $options['php_cmd'] = PosixTools::which('php');
            }
            system(trim($options['php_cmd'].' symfony plugin:publish-assets'));
          }
          else
          {
            log_message(Color::style('bright') . Color::fgColor('green')
              . 'Creating symbolic links for plugins... (internal method)'
              . Color::style('normal'));
            foreach ($this->findPlugins($project_path) as $name)
            {
              $link = 'web/'.$name;
              $target = $project_path.'/plugins/'.$name.'/web';
              // Ignore if there is a real directory with this name
              if (is_link($project_path.'/'.$link) || !is_dir($project_path.'/'.$link))
              {
                $this->replaceSymlink($project_path, $target, $link, $options['relative']);
              }
            }
          }
        }

    }

    /**
     * @pram string $project_path Absolute project path
     * @param string $target The destination of the symlink
     * @param string $link The relative path of the symlink to create
     * @param boolean $relative Try to use a relative destination
     * @return boolean Success
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function replaceSymlink($project_path, $target, $link, $relative = true)
    {
        if ($relative) {
            $target = FileTools::calculateRelativeDir($project_path . '/' . $link, $target);
        }

        FileTools::mkdirs(dirname($project_path . '/' . $link));
        $success = FileTools::symlink($target, $project_path . '/' . $link);

        log_message('  ' . $link . ' => ' . $target . ($success ? '' : ' ...FAILED!'), !$success);
    }

    /**
     * Find plugins with a "web" directory
     * @param string $project_path
     * @return array Plugin names
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function findPlugins($project_path)
    {
        $plugins = array();
        foreach (new \DirectoryIterator($project_path . "/plugins") as $file)
        {
            $name = $file->getFilename();
            if ($file->isDir()
                && preg_match('/^[^\.].+Plugin$/', $name)
                && is_dir($project_path . '/plugins/' . $name . '/web')
            ) {
                $plugins[] = $name;
            }
        }

        return $plugins;
    }
}
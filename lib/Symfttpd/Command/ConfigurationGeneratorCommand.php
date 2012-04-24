<?php
/**
 * ConfigurationGenerator class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/10/11
 */

namespace Symfttpd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ConfigurationGeneratorCommand extends Command
{
    /**
     * @return void
     */
    public function configure()
    {
        $this->setName('genconf');
        $this->setDescription('Generates symfttpd configuration file (lighttpd format).');
        $this->addOption('default', null, InputOption::VALUE_OPTIONAL, 'Change the default application.', 'index');
        $this->addOption('only',    null, InputOption::VALUE_OPTIONAL, 'Do not allow any other application.', false);
        $this->addOption('allow',   null, InputOption::VALUE_OPTIONAL, 'Useful with `only`, allow some other applications (useful for allowing a _dev alternative, for example).', false);
        $this->addOption('nophp',   null, InputOption::VALUE_OPTIONAL, 'Path of the web directory. Autodected to ../web if not present.', 'uploads');
        $this->addOption('path',    null, InputOption::VALUE_OPTIONAL, 'Deny PHP execution in the specified directories (default being uploads).', getcwd().'/../web');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting generating symfttpd configuration.');

        $fileName = 'lighttpd.php';
        $allow    = explode(',', $input->getOption('allow'));
        $nophp    = explode(',', $input->getOption('nophp'));
        $path     = realpath($input->getOption('path'));

        $files = array(
            'dir'  => array(),
            'php'  => array(),
            'file' => array()
        );

        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('Directory "%s" not found.', $input->getOption('path')));
        }

        foreach (new \DirectoryIterator($path) as $file)
        {
          $name = $file->getFilename();
          if ($name[0] != '.')
          {
            if ($file->isDir())
            {
              $files['dir'][] = $name;
            }
            elseif (!preg_match('/\.php$/', $name))
            {
              $files['file'][] = $name;
            }
            elseif (empty($options['only']))
            {
                $files['php'][] = $name;
            }
          }
        }

        foreach ($allow as $name)
        {
          $files['php'][] = $name.'.php';
        }

        $output->writeln(sprintf('Generate %s in "%s".', $fileName, $path));

        $generated = $this->generateConfiguration($fileName, $path, array(
            'path'    => $path,
            'nophp'   => $nophp,
            'default' => $input->getOption('default'),
            'php'     => $files['php'],
            'file'    => $files['file'],
            'dir'     => $files['dir'],
        ));

        if ($generated) {
            $output->writeln('The configuration file has been well generated.');
        } else {
            $output->writeln('An error occured while file generation.');
        }
    }

    /**
     * Generates the symfttpd configuration file.
     *
     * @param $path
     * @param array $parameters Parameters are used in the template.
     * @return boolean
     */
    protected function generateConfiguration($file, $path, $parameters = array())
    {
        ob_start();
        require __DIR__.'/../Resources/templates/'.$file;
        $template = ob_get_clean();

        return file_put_contents($path.'/'.$file, $template) > 0;
    }
}
<?php
/**
 * ConfigurationGenerator class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/10/11
 */

namespace Symfttpd\Command;

use Symfttpd\Util\Filesystem;
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
        $this->setDescription('Generates symfttpd configuration file.');
        //$this->addArgument('type', InputArgument::REQUIRED, 'Type of project you want to setup.', 'Symfony');
        $this->addOption('default', 'd', InputOption::VALUE_OPTIONAL, 'Change the default application.', 'index');
        $this->addOption('only',    'o', InputOption::VALUE_OPTIONAL, 'Do not allow any other application.', false);
        $this->addOption('allow',   'a', InputOption::VALUE_OPTIONAL, 'Useful with `only`, allow some other applications (useful for allowing a _dev alternative, for example).', false);
        $this->addOption('nophp',   'n', InputOption::VALUE_OPTIONAL, 'Path of the web directory. Autodected to ../web if not present.', 'uploads');
        $this->addOption('path',    'p', InputOption::VALUE_OPTIONAL, 'Deny PHP execution in the specified directories (default being uploads).', getcwd().'/../web');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $allow = explode(',', $input->getOption('allow'));
        $nophp = explode(',', $input->getOption('nophp'));
        $path  = realpath($input->getOption('path'));

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

        $this->generateConfiguration($input->getOption('path'), array(
            'path'    => $path,
            'nophp'   => $nophp,
            'default' => $input->getOption('default'),
            'php'     => $files['php'],
            'file'    => $files['file'],
            'dir'     => $files['dir'],
        ));
    }

    /**
     * Generates the symfttpd configuration file.
     *
     * @param $path
     * @param array $parameters
     * @return void
     */
    protected function generateConfiguration($path, $parameters = array())
    {
        $fileName = 'symfttpd.conf.php';
        $template = __DIR__.'/../Resources/templates/'.$fileName;
        file_put_contents($fileName, require $template);
    }
}
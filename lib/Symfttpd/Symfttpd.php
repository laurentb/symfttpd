<?php
/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 27/04/12
 */

namespace Symfttpd;

use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Factory;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfttpd extends \Pimple
{
    const VERSION = '2.0.0-beta';

    /**
     * @param Configuration\SymfttpdConfiguration $configuration
     */
    public function __construct(SymfttpdConfiguration $configuration = null)
    {
        $container = $this;

        if (null == $configuration) {
          $configuration = new SymfttpdConfiguration();
        }

        $this['configuration'] = $configuration;

        $this['project'] = $this->share(function ($c) use ($container) {
            $config = $container['configuration'];

            $project = Factory::createProject(
                $config->getProjectType(),
                $config->getProjectVersion(),
                $config->getProjectOptions()
            );

            // The root directory is where Symfttpd is running.
            $project->setRootDir(getcwd());

            return $project;
        });

      $this['server'] = $this->share(function ($c) use ($container) {
          $config = $container['configuration'];

          $server = Factory::createServer($config->getServerType(), $container['project']);

          // BC with the 1.0 configuration version
          if ($server instanceof \Symfttpd\Server\Lighttpd && $config->has('lighttpd_cmd')) {
            $server->setCommand($config->get('lighttpd_cmd'));
          }
      });
    }

    /**
     * Return the Symfttpd configuration.
     *
     * @return Configuration\SymfttpdConfiguration
     */
    public function getConfiguration()
    {
        return $this['configuration'];
    }

    /**
     * Return the project.
     *
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        return $this['project'];
    }

    /**
     * The server used by Symfttpd.
     *
     * @param null $path The path to initialize the server instance if needed.
     * @return Server\Lighttpd
     */
    public function getServer()
    {
        return $this['server'];
    }

    /**
     * Find executables.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function findExecutables()
    {
        $this->findPhpCmd();
        $this->findPhpCgiCmd();
    }

    /**
     * Set the php command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpCmd()
    {
        if (false === $this['configuration']->has('php_cmd')) {
            $phpFinder = new PhpExecutableFinder();
            $cmd = $phpFinder->find();

            if (null == $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php executable not found');
            }

            $this['configuration']->set('php_cmd', $cmd);
        }
    }

    /**
     * Set the php-cgi command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpCgiCmd()
    {
        if (false === $this['configuration']->has('php_cgi_cmd')) {
            $exeFinder = new ExecutableFinder();
            $exeFinder->addSuffix('');
            $cmd = $exeFinder->find('php-cgi');

            if (null == $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php-cgi executable not found.');
            }

            $this['configuration']->set('php_cgi_cmd', $cmd);
        }
    }
}

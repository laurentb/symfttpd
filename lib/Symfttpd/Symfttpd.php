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

namespace Symfttpd;

use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Project\ProjectInterface;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfttpd\TwigExtension;

/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfttpd extends \Pimple
{
    const VERSION = '@package_version@';

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
            $config  = $container['configuration'];
            $type    = $config->getProjectType();
            $version = $config->getProjectVersion();

            $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type).str_replace(array('.', '-', 'O'), '', $version));

            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('"%s" in version "%s" is not supported.', $type, $version));
            }

            return new $class(new OptionBag($config->getProjectOptions()), getcwd());
        });

        $this['server'] = $this->share(function ($c) use ($container) {
            $config = $container['configuration'];
            $type   = $config->getServerType();

            $class = sprintf('Symfttpd\\Server\\%s', ucfirst($type));

            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('"%s" is not supported.', $type));
            }

            // Create the server class.
            $server = new $class(
                $container['project'],
                $container['twig'],
                $container['loader'],
                $container['writer'],
                new OptionBag($config->getServerOptions())
            );

            // BC with the 1.0 configuration version
            if ($server instanceof \Symfttpd\Server\Lighttpd && $config->has('lighttpd_cmd')) {
                $server->setCommand($config->get('lighttpd_cmd'));
            }

            return $server;
        });

        $this['twig'] = $this->share(function ($c) use ($container) {
            $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(array(__DIR__.'/Resources/templates')), array(
                'debug'            => true,
                'strict_variables' => true,
                'auto_reload'      => true,
                'cache'            => false,
            ));

            $twig->addExtension(new TwigExtension());

            return $twig;
        });

        $this['loader'] = $this->share(function ($c) use ($container) {
            return new Loader();
        });

        $this['writer'] = $this->share(function ($c) use ($container) {
            return new Writer();
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

            if (false == $cmd) {
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

            if (false == $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php-cgi executable not found.');
            }

            $this['configuration']->set('php_cgi_cmd', $cmd);
        }
    }
}

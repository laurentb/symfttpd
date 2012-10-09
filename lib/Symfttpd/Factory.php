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

use Symfony\Component\Config\Definition\Processor;
use Symfttpd\Config;
use Symfttpd\Configuration\Configuration;
use Symfttpd\Project\ProjectInterface;

/**
 * Factory description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Factory
{
    /**
     * @return Config
     */
    public function createConfig()
    {
        $file = new SymfttpdFile();
        $file->setProcessor(new Processor());
        $file->setConfiguration(new Configuration());

        $config = new Config();
        $config->merge($file->read());

        return $config;
    }

    /**
     * Create a Symfttpd instance
     *
     * @param array $localConfig
     *
     * @return Symfttpd
     */
    public function createSymfttpd(array $localConfig = array())
    {
        $config = $this->createConfig();
        $config->merge($localConfig);

        $project = $this->createProject($config);
        $server  = $this->createServer($config, $project);

        $symfttpd = new Symfttpd();
        $symfttpd->setConfig($config);
        $symfttpd->setProject($project);
        $symfttpd->setServer($server);

        return $symfttpd;
    }

    /**
     * Create a Project instance
     *
     * @param \Symfttpd\Config $config
     *
     * @return \Symfttpd\Project\ProjectInterface
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function createProject(Config $config)
    {
        // Find the type of framework if any.
        if ($config->has('want')) {
            $type = "symfony";
        } else {
            $type = $config->get('project_type', 'php');
        }

        // Find the version of the framework if any.
        if ($type !== 'php' && !$config->has('project_version')) {
            if ($config->has('want')) {
                $version = substr($config->get('want'), 0, 1);
            } else {
                throw new \RuntimeException('A project version must be set in the symfttpd.conf.php file.');
            }
        } else {
            $version = $config->get('project_version');
        }

        $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type) . str_replace(array('.', '-', 'O'), '', $version));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" in version "%s" is not supported.', $type, $version));
        }

        return new $class($config, getcwd());
    }

    /**
     * Create a Server instance
     *
     * @param \Symfttpd\Config                   $config
     * @param \Symfttpd\Project\ProjectInterface $project
     *
     * @return \Symfttpd\Server\ServerInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createServer(Config $config, ProjectInterface $project)
    {
        $type = $config->get('server_type', 'lighttpd');

        $class = sprintf('Symfttpd\\Server\\%s', ucfirst($type));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not supported.', $type));
        }

        // Define configuration template storage paths.
        $dirs = array_merge(array(__DIR__ . '/Resources/templates'), $config->get('templates_dirs', array()));

        // Configure Twig for the rendering of configuration files.
        $twig = new \Twig_Environment(
            new \Twig_Loader_Filesystem($dirs),
            array(
                'debug'            => true,
                'strict_variables' => true,
                'auto_reload'      => true,
                'cache'            => false,
            )
        );

        $twig->addExtension(new TwigExtension());

        return new $class($project, $twig, new Loader(), new Writer(), $config);
    }

    /**
     * Create an initialized Symfttpd instance.
     *
     * @param array $config
     *
     * @return Symfttpd
     */
    public static function create(array $config = array())
    {
        $factory = new static();

        $symfttpd = $factory->createSymfttpd($config);

        return $symfttpd;
    }
}

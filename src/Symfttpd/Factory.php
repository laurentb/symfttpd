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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Config;
use Symfttpd\Configuration;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfttpd\Guesser\Exception\UnguessableException;
use Symfttpd\Guesser\ProjectGuesser;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;

/**
 * Factory description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Factory
{
    /**
     * @var \Symfony\Component\Process\ExecutableFinder
     */
    public $execFinder;

    /**
     * @var \Symfttpd\Guesser\ProjectGuesser
     */
    public $projectGuesser;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Process\ExecutableFinder $execFinder
     * @param \Symfttpd\Guesser\ProjectGuesser            $projectGuesser
     */
    public function __construct(ExecutableFinder $execFinder, ProjectGuesser $projectGuesser)
    {
        $this->execFinder = $execFinder;
        $this->projectGuesser = $projectGuesser;
    }

    /**
     * Create an initialized Symfttpd instance.
     *
     * @param array $config
     *
     * @return Symfttpd
     */
    public function create(array $config = array())
    {
        $symfttpd = $this->createSymfttpd($config);

        return $symfttpd;
    }

    /**
     * Create a Symfttpd instance
     *
     * @param array $localConfig
     *
     * @return \Symfttpd\Symfttpd
     */
    public function createSymfttpd(array $localConfig = array())
    {
        $config = $this->createConfig();
        $config->merge($localConfig);

        $project   = $this->createProject($config);
        $server    = $this->createServer($config, $project);
        $generator = $this->createGenerator($config, $server, $project);

        $symfttpd = new Symfttpd();
        $symfttpd->setConfig($config);
        $symfttpd->setProject($project);
        $symfttpd->setServer($server);
        $symfttpd->setGenerator($generator);

        return $symfttpd;
    }

    /**
     * @return \Symfttpd\Config
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
        if (!$config->has('project_type')) {
            try {
                list($type, $version) = $this->projectGuesser->guess();
            } catch (UnguessableException $e) {
                $type = 'php';
                $version = null;
            }
        } else {
            $type = $config->get('project_type');
            $version = substr($config->get('project_version'), 0, 1);
        }

        $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type) . str_replace(array('.', '-', 'O'), '', $version));

        if (!class_exists($class)) {
            if (!$version) {
                $message = sprintf('"%s"', $type);
            } else {
                $message = sprintf('"%s" (with version "%s")', $type, $version);
            }

            throw new \InvalidArgumentException(sprintf('%s is not supported.', $message));
        }

        return new $class($config);
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
     * @throws Exception\ExecutableNotFoundException
     */
    public function createServer(Config $config, ProjectInterface $project)
    {
        $type = $config->get('server_type', 'lighttpd');

        $class = sprintf('Symfttpd\\Server\\%s', ucfirst($type));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not supported.', $type));
        }

        /** @var \Symfttpd\Server\ServerInterface $server */
        $server = new $class();
        $server->bind($config->get('server_address', '127.0.0.1'), $config->get('server_port', '4042'));

        if ($config->has('server_cmd')) {
            $server->setCommand($config->get('server_cmd'));
        } else {
            $this->execFinder->addSuffix('');

            // Try to guess the executable command of the server.
            if (null == $cmd = $this->execFinder->find($type)) {
                throw new ExecutableNotFoundException($type.' executable not found.');
            }

            $server->setCommand($cmd);
        }

        $server->configure($config, $project);
        $server->setGateway($this->createGateway($config));

        return $server;
    }

    /**
     * @param \Symfttpd\Config                   $config
     * @param \Symfttpd\Server\ServerInterface   $server
     * @param \Symfttpd\Project\ProjectInterface $project
     *
     * @return \Symfttpd\ConfigurationGenerator
     * @throws \InvalidArgumentException
     */
    public function createGenerator(Config $config, ServerInterface $server, ProjectInterface $project)
    {
        $dirs = array(__DIR__ . '/Resources/templates/');
        $dirs += $config->get('server_templates_dirs', array());

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

        $filesystem = new Filesystem();

        $generator = new \Symfttpd\ConfigurationGenerator($twig, $filesystem);
        $generator->setPath($config->get('server_config_path', $config->get('symfttpd_dir') . '/conf'));

        return $generator;
    }

    /**
     * @param Config $config
     *
     * @return Gateway\GatewayInterface
     * @throws \InvalidArgumentException
     */
    public function createGateway(Config $config)
    {
        $type = $config->get('gateway_type', 'fastcgi');

        // @todo find a better way...
        $mapping = array(
            'fastcgi' => '\Symfttpd\Gateway\Fastcgi',
            'php-fpm' => '\Symfttpd\Gateway\PhpFpm',
        );

        if (!array_key_exists($type, $mapping)) {
            throw new \InvalidArgumentException(sprintf('"%s" gateway is not supported.', $type));
        }

        $class = $mapping[$type];

        /** @var \Symfttpd\Gateway\GatewayInterface $gateway */
        $gateway = new $class();
        $gateway->setCommand($config->get('gateway_cmd', $config->get('php_cgi_cmd')));

        return $gateway;
    }
}

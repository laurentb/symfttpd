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

namespace Symfttpd\Console;

use Monolog\Handler\StreamHandler;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Console\Command\GenconfCommand;
use Symfttpd\Console\Command\SelfupdateCommand;
use Symfttpd\Console\Command\SpawnCommand;
use Symfttpd\Config;
use Symfttpd\Configuration;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfttpd\Guesser\Checker\Symfony2Checker;
use Symfttpd\Guesser\Checker\Symfony1Checker;
use Symfttpd\Guesser\Exception\UnguessableException;
use Symfttpd\Guesser\ProjectGuesser;
use Symfttpd\Log\Logger;
use Symfttpd\Symfttpd;
use Symfttpd\SymfttpdFile;

/**
 * Application class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Application extends BaseApplication
{
    /**
     * @var \Pimple
     */
    protected $container;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('Symfttpd', Symfttpd::VERSION);

        // Add options to Symfttpd globally
        $this->getDefinition()->addOption(new InputOption('--debug', null, InputOption::VALUE_NONE, 'Switches on debug mode.'));

        $this->container = $c = new \Pimple();

        $c['debug'] = false;

        // Configure the project guesser.
        $c['project.symfony1_checker'] = $c->share(function () {
            return new Symfony1Checker();
        });

        $c['project.symfony2_checker'] = $c->share(function () {
            return new Symfony2Checker();
        });

        $c['project.guesser'] = $c->share(function ($c) {
            $guesser = new ProjectGuesser();
            $guesser->registerChecker($c['project.symfony1_checker']);
            $guesser->registerChecker($c['project.symfony2_checker']);

            return $guesser;
        });

        $c['finder'] = $c->share(function ($c) {
            return new ExecutableFinder();
        });

        $c['symfttpd_file'] = $c->share(function ($c) {
            $file = new SymfttpdFile();
            $file->setProcessor(new Processor());
            $file->setConfiguration(new Configuration());

            return $file;
        });

        $c['config'] = $c->share(function ($c) {
            $config = new Config();
            $config->merge($c['symfttpd_file']->read());

            if (!$config->has('symfttpd_dir')) {
                $config->get('symfttpd_dir', getcwd().'/symfttpd');
            }

            return $config;
        });

        $c['twig'] = $c->share(function ($c) {
            $dirs = array(__DIR__ . '/../Resources/templates/');
            $dirs += $c['config']->get('server_templates_dirs', array());

            return new \Twig_Environment(
                new \Twig_Loader_Filesystem($dirs),
                array(
                    'debug'            => true,
                    'strict_variables' => true,
                    'auto_reload'      => true,
                    'cache'            => false,
                )
            );
        });

        $c['filesystem'] = $c->share(function ($c) {
            return new Filesystem();
        });

        $c['generator'] = $c->share(function ($c) {
            $config = $c['config'];
            $generator = new \Symfttpd\ConfigurationGenerator($c['twig'], $c['filesystem'], $c['logger']);
            $generator->setPath($config->get('server_config_path', $config->get('symfttpd_dir') . '/conf'));

            return $generator;
        });

        $c['project'] = $c->share(function ($c) {
            /** @var $config \Symfttpd\Config */
            $config = $c['config'];

            if (!$config->has('project_type')) {
                try {
                    list($type, $version) = $c['project.guesser']->guess();
                } catch (UnguessableException $e) {
                    $type = 'php';
                    $version = null;
                }
            } else {
                $type = $config->get('project_type', null);
                $version = substr($config->get('project_version', null), 0, 1);
            }

            $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type) . str_replace(array('.', '-', 'O'), '', $version));

            if (!class_exists($class)) {
                if (!$version) {
                    $message = sprintf('"%s"', $type);
                } else {
                    $message = sprintf('"%s" (with version "%s")', $type, $version);
                }

                throw new \InvalidArgumentException(sprintf('Project %s is not supported.', $message));
            }

            // @todo create a configure method in the project to not inject anything in the constructor.
            return new $class($config);
        });

        $c['server'] = $c->share(function ($c) {
            /** @var $config \Symfttpd\Config */
            $config = $c['config'];

            $server = new \Symfttpd\Server\Server();
            $server->configure($config, $c['project']);

            if (null == $cmd = $config->get('server_cmd')) {
                $c['finder']->addSuffix('');

                // Try to guess the executable command of the server.
                if (null == $cmd = $c['finder']->find($server->getType())) {
                    throw new ExecutableNotFoundException($server->getType().' executable not found.');
                }
            }

            $server->setExecutable($cmd);
            $server->setGateway($c['gateway']);
            $server->setProcessBuilder($c['process_builder']);
            $server->setLogger($c['logger']);

            return $server;
        });

        $c['gateway'] = $c->share(function ($c) {
            /** @var $config \Symfttpd\Config */
            $config = $c['config'];
            $type = $config->get('gateway_type', 'fastcgi');

            // @todo find a better way...
            $mapping = array(
                \Symfttpd\Gateway\Fastcgi::TYPE_FASTCGI => '\Symfttpd\Gateway\Fastcgi',
                \Symfttpd\Gateway\PhpFpm::TYPE_PHPFPM   => '\Symfttpd\Gateway\PhpFpm',
            );

            if (!array_key_exists($type, $mapping) || !class_exists($mapping[$type])) {
                throw new \InvalidArgumentException(sprintf('"%s" gateway is not supported.', $type));
            }

            $class = $mapping[$type];

            /** @var \Symfttpd\Gateway\GatewayInterface $gateway */
            $gateway = new $class();

            // @todo guess the command
            $gateway->configure($config);
            $gateway->setProcessBuilder($c['process_builder']);
            $gateway->setLogger($c['logger']);

            return $gateway;
        });

        $c['process_builder'] = function ($c) {
            $pb = new \Symfony\Component\Process\ProcessBuilder();
            $pb->setTimeout(null);

            return $pb;
        };

        $c['logger'] = $c->share(function ($c) {
            $level = Logger::ERROR;

            if (true === $c['debug']) {
                $level = Logger::DEBUG;
            }

            $logger = new Logger($this->getName());
            $logger->pushHandler(new StreamHandler($c['config']->get('symfttpd_dir').'/log/symfttpd.log', $level));

            return $logger;
        });

        $c['watcher'] = $c->share(function ($c) {
            $watcher = new \Symfttpd\Watcher\Watcher();
            $watcher->setLogger($c['logger']);

            return $watcher;
        });
    }

    /**
     * Return the service container
     *
     * @return \Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @param $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Initializes Symfttpd commands.
     *
     * @return array
     */
    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new GenconfCommand();
        $commands[] = new SpawnCommand();
        $commands[] = new SelfupdateCommand();

        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption('--debug')) {
            $this->container['debug'] = true;
        }

        return parent::doRun($input, $output);
    }
}

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
use Symfttpd\Guesser\Exception\UnguessableException;
use Symfttpd\Guesser\ProjectGuesser;
use Symfony\Component\Process\ExecutableFinder;
use Symfttpd\Config;
use Symfttpd\Configuration\Configuration;
use Symfttpd\Exception\ExecutableNotFoundException;
use Symfttpd\Filesystem\Filesystem;
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
        $generator = $this->createServerConfiguration($config, $server, $project);

        $symfttpd = new Symfttpd();
        $symfttpd->setConfig($config);
        $symfttpd->setProject($project);
        $symfttpd->setServer($server);
        $symfttpd->setServerConfiguration($generator);

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
            $config->set('project_type', 'symfony');
            $config->set('project_version', substr($config->get('want'), 0, 1));
        }

        if (!$config->has('project_type')) {
            try {
                list($type, $version) = $this->projectGuesser->guess();
            } catch (UnguessableException $e) {
                $type = 'php';
                $version = null;
            }
        } else {
            $type = $config->get('project_type');
            $version = $config->get('project_version');
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

        /**
         * @var \Symfttpd\Server\ServerInterface $server
         */
        $server = new $class();
        $server->bind($config->get('server_address', '127.0.0.1'), $config->get('server_port', '4042'));

        // BC
        if ('lighttpd' == $type && $config->has('lighttpd_cmd')) {
            $server->setCommand($config->get('lighttpd_cmd'));
        } else {
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
        }

        // Configure logging directory
        $logDir = $config->get('server_log_dir', $project->getLogDir() . '/' . $server->getName());
        $server->setErrorLog($logDir . '/' . $config->get('server_error_log', 'error.log'));
        $server->setAccessLog($logDir . '/' . $config->get('server_access_log', 'access.log'));

        $server->setFastcgi($config->get('php_cgi_cmd'));
        $server->setPidfile($project->getCacheDir() . '/' . $server->getName(). '/' . $config->get('server_pidfile', '.sf'));

        // Configure project relative directories and files
        $server->setDocumentRoot($project->getWebDir());
        $server->setIndexFile($project->getIndexFile());
        $server->setAllowedDirs($config->get('project_readable_dirs', $project->getDefaultReadableDirs()));
        $server->setAllowedFiles($config->get('project_readable_files', $project->getDefaultReadableFiles()));
        $server->setExecutableFiles($config->get('project_readable_phpfiles', $project->getDefaultExecutableFiles()));
        $server->setUnexecutableDirs($config->get('project_nophp', array()));

        return $server;
    }

    /**
     * @param \Symfttpd\Config                   $config
     * @param \Symfttpd\Server\ServerInterface   $server
     * @param \Symfttpd\Project\ProjectInterface $project
     *
     * @return \Symfttpd\Server\Configuration\ConfigurationInterface
     * @throws \InvalidArgumentException
     */
    public function createServerConfiguration(Config $config, ServerInterface $server, ProjectInterface $project)
    {
        $type = $config->get('server_type', 'lighttpd');

        $class = sprintf('Symfttpd\\Server\\Configuration\\%s', ucfirst($type));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not supported.', $type));
        }

        // Define configuration template storage paths.
        $dirs = array_merge(
            array(__DIR__ . '/Resources/templates/' . $server->getName()),
            $config->get('server_templates_dirs', array())
        );

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

        $filesystem = new Filesystem();

        $configuration = new $class($twig, $filesystem);
        $configuration->setTemplate($config->get('server_template', $server->getName() . '.conf.twig'));

        $defaultPath = $project->getCacheDir() . '/' . $server->getName(). '/' . $server->getName() . '.conf';
        $configuration->setPath($config->get('server_config_path', $defaultPath));

        return $configuration;
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
}

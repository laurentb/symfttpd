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

        $project   = $this->createProject($config);
        $server    = $this->createServer($config, $project);
        $generator = $this->createServerGenerator($config, $server, $project);

        $symfttpd = new Symfttpd();
        $symfttpd->setConfig($config);
        $symfttpd->setProject($project);
        $symfttpd->setServer($server);
        $symfttpd->setServerGenerator($generator);

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

        /**
         * @var \Symfttpd\Server\ServerInterface $server
         */
        $server = new $class();
        $server->bind($config->get('server_address', '127.0.0.1'), $config->get('server_port', '4042'));

        // Configure logging directory
        $logDir = $config->get('server_log_dir', $project->getLogDir() . '/' . $server->getName());
        $server->setErrorLog($logDir . '/' . $config->get('server_error_log', 'error.log'));
        $server->setAccessLog($logDir . '/' . $config->get('server_access_log', 'access.log'));

        $server->setFastcgi($config->get('php_cgi_cmd'));
        $server->setPidfile($project->getCacheDir() . '/' . $config->get('server_pidfile', '.sf'));

        // Configure project relative directories and files
        $server->setDocumentRoot($project->getWebDir());
        $server->setIndexFile($project->getIndexFile());
        $server->setAllowedDirs($config->get('project_readable_dirs', array()));
        $server->setAllowedFiles($config->get('project_readable_files', array()));
        $server->setExecutableFiles($config->get('project_readable_phpfiles', array()));
        $server->setUnexecutableDirs($config->get('project_nophp', array()));

        return $server;
    }

    /**
     * @param \Symfttpd\Config                   $config
     * @param \Symfttpd\Server\ServerInterface   $server
     * @param \Symfttpd\Project\ProjectInterface $project
     *
     * @return \Symfttpd\Server\Generator\GeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function createServerGenerator(Config $config, ServerInterface $server, ProjectInterface $project)
    {
        $type = $config->get('server_type', 'lighttpd');

        $class = sprintf('Symfttpd\\Server\\Generator\\%sGenerator', ucfirst($type));

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

        $generator = new $class($twig, $filesystem);
        $generator->setTemplate($config->get('server_template', $server->getName() . '.conf.twig'));

        $defaultPath = $project->getCacheDir() . '/' . $server->getName(). '/' . $server->getName() . '.conf';
        $generator->setPath($config->get('server_config_path', $defaultPath));

        return $generator;
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

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
class Symfttpd
{
    const VERSION = '2.0.0-beta';

    /**
     * @var \Symfttpd\Configuration\SymfttpdConfiguration
     */
    protected $configuration;

    /**
     * @var \Symfttpd\Project\ProjectInterface
     */
    protected $project;

    /**
     * @var \Symfttpd\Server\ServerInterface
     */
    protected $server;

    /**
     * @param Configuration\SymfttpdConfiguration $configuration
     */
    public function __construct(SymfttpdConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?: new SymfttpdConfiguration();
    }

    /**
     * Return the Symfttpd configuration.
     *
     * @return Configuration\SymfttpdConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Return the type of the project.
     * If the project is a Symfony2 one, it will return Symfony.
     * This value is set in the configuration file.
     *
     * @return string
     */
    public function getProjectType()
    {
        // BC with the 1.1 configuration version
        if (true == $this->configuration->has('want')
            && false == $this->configuration->has('project_type')) {
            return "symfony";
        }

        if (false == $this->configuration->has('project_type')) {
            throw new \RuntimeException('A project type must be set in the symfttpd.conf.php file.');
        }

        return $this->configuration->get('project_type');
    }

    /**
     * Return the project version.
     * For a symfony project it can be 1.4 or 2.0 (which
     * is the same as 2), even 2.1.
     *
     * @return mixed|null
     */
    public function getProjectVersion()
    {
        // BC with the 1.0 configuration version
        if (true == $this->configuration->has('want')
            && false == $this->configuration->has('project_version')) {
            return $this->configuration->get('want');
        }

        if (false == $this->configuration->has('project_version')) {
            throw new \RuntimeException('A project version must be set in the symfttpd.conf.php file.');
        }

        return $this->configuration->get('project_version', '');
    }

    /**
     * Return the project.
     *
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        if (null == $this->project) {
            $this->project = Factory::createProject($this->getProjectType(), $this->getProjectVersion());
        }

        return $this->project;
    }

    /**
     * The server used by Symfttpd.
     *
     * @param null $path The path to initialize the server instance if needed.
     * @return Server\Lighttpd
     */
    public function getServer()
    {
        if (null == $this->server) {
            $this->server = Factory::createServer($this->getServerType(), $this->getProject());

            // BC with the 1.0 configuration version
            if ($this->server instanceof \Symfttpd\Server\Lighttpd
                && $this->configuration->has('lighttpd_cmd')) {
                $this->server->setCommand($this->configuration->get('lighttpd_cmd'));
            }
        }

        return $this->server;
    }

    /**
     * Return the type of the server.
     *
     * @return mixed|null|string
     */
    public function getServerType()
    {
        // BC with 1.0 version
        if (true == $this->configuration->has('lighttpd_cmd')
            && false == $this->configuration->has('server_type')) {
            return 'lighttpd';
        }

        return $this->configuration->get('server_type', 'lighttpd');
    }

    /**
     * Set the project.
     *
     * @param \Symfttpd\Project\ProjectInterface $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * Set the server.
     *
     * @param \Symfttpd\Server\ServerInterface $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }

    /**
     * Find executables.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function findExecutables()
    {
        $this->findPhpCmd();
        $this->findPhpcgiCmd();
    }

    /**
     * Set the php command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpCmd()
    {
        if (false === $this->configuration->has('php_cmd')) {
            $phpFinder = new PhpExecutableFinder();
            $cmd = $phpFinder->find();

            if (null == $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php executable not found');
            }

            $this->configuration->set('php_cmd', $cmd);
        }
    }

    /**
     * Set the php-cgi command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findPhpcgiCmd()
    {
        if (false === $this->configuration->has('php_cgi_cmd')) {
            $exeFinder = new ExecutableFinder();
            $exeFinder->addSuffix('');
            $cmd = $exeFinder->find('php-cgi');

            if (null == $cmd) {
                throw new \Symfttpd\Exception\ExecutableNotFoundException('php-cgi executable not found.');
            }

            $this->configuration->set('php_cgi_cmd', $cmd);
        }
    }
}

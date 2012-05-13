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
     * Return the project.
     *
     * @return \Symfttpd\Project\ProjectInterface
     */
    public function getProject()
    {
        if (null == $this->project) {
            $this->project = Factory::createProject(
                $this->configuration->getProjectType(),
                $this->configuration->getProjectVersion(),
                $this->configuration->getProjectOptions()
            );

            // The root directory is where Symfttpd is running.
            $this->project->setRootDir(getcwd());
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
            $this->server = Factory::createServer($this->configuration->getServerType(), $this->getProject());

            // BC with the 1.0 configuration version
            if ($this->server instanceof \Symfttpd\Server\Lighttpd
                && $this->configuration->has('lighttpd_cmd')) {
                $this->server->setCommand($this->configuration->get('lighttpd_cmd'));
            }
        }

        return $this->server;
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

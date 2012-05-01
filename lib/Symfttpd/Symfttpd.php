<?php
/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 27/04/12
 */

namespace Symfttpd;

use Symfttpd\Configuration\SymfttpdConfiguration;
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
    protected $coniguration;

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
     * Find executables.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    public function findExecutables()
    {
        $this->findServerCmd();
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
                throw new ExecutableNotFoundException('php executable not found');
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
                throw new ExecutableNotFoundException('php-cgi executable not found.');
            }

            $this->configuration->set('php_cgi_cmd', $cmd);
        }
    }

    /**
     * Set the server command value in the Symfttpd option
     * if it is not already set.
     *
     * @throws \Symfttpd\Exception\ExecutableNotFoundException
     */
    protected function findServerCmd()
    {
        if (false === $this->configuration->has('lighttpd_cmd')) {
            $this->configuration->set('lighttpd_cmd', $this->server->getCommand());
        }
    }

    /**
     * @return mixed|null
     */
    public function getServerCmd()
    {
        // Find the lighttpd command
        $this->findServerCmd();

        return $this->configuration->get('lighttpd_cmd');
    }

    /**
     * The server used by Symfttpd.
     *
     * @param null $path The path to initialize the server instance if needed.
     * @return Server\Lighttpd
     */
    public function getServer($path = null)
    {
        if (null == $this->server) {
            $this->server = new \Symfttpd\Server\Lighttpd($path);
        }

        return $this->server;
    }
}

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

namespace Symfttpd\Gateway;

use Symfttpd\Config;
use Symfttpd\ConfigurationGenerator;
use Symfttpd\ProcessAwareInterface;

/**
 * GatewayInterface
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface GatewayInterface extends ProcessAwareInterface
{
    /**
     * Return the type of gateway.
     *
     * @return string
     */
    public function getType();

    /**
     * Configure the gateway with settings of the
     * Symfttpd configuration file.
     *
     * @param \Symfttpd\Config $config
     */
    public function configure(Config $config);

    /**
     * Return the executable used to run the gateway.
     *
     * @return String
     */
    public function getExecutable();

    /**
     * Return the socket of the gateway used by the server.
     *
     * @return string
     */
    public function getSocket();

    /**
     * The pidfile is used to kill the process.
     *
     * @return string
     */
    public function getPidfile();

    /**
     * Start the gateway.
     * @param \Symfttpd\ConfigurationGenerator $generator
     *
     * @return mixed
     * @throws \RuntimeException When the gateway failed to start.
     */
    public function start(ConfigurationGenerator $generator);

    /**
     * Stop the gateway.
     *
     * @return mixed
     */
    public function stop();
}

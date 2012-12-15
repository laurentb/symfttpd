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
use Symfttpd\ConfigurationFile\ConfigurationFileInterface;

/**
 * GatewayInterface
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface GatewayInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param \Symfttpd\Config $config
     */
    public function configure(Config $config);

    /**
     * @param $command
     */
    public function setCommand($command);

    /**
     * @return String
     */
    public function getCommand();

    /**
     * @param \Symfttpd\ConfigurationFile\ConfigurationFileInterface $configurationFile
     */
    public function setConfigurationFile(ConfigurationFileInterface $configurationFile);

    /**
     * @return \Symfttpd\ConfigurationFile\ConfigurationFileInterface
     */
    public function getConfigurationFile();

    /**
     * @param $socket
     */
    public function setSocket($socket);

    /**
     * @return string
     */
    public function getSocket();
}

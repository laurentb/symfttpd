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

namespace Symfttpd\Configuration;

/**
 * ServerConfigurationInterface interface
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
interface ServerConfigurationInterface
{
    /**
     * Read the configuration.
     *
     * @abstract
     * @param SymfttpdConfiguration $configuration
     * @return mixed
     */
    public function generate(SymfttpdConfiguration $configuration);

    /**
     * Write the configuration in the directory.
     *
     * @abstract
     * @return mixed
     * @throws Exception\ConfigurationException
     */
    public function write();
}

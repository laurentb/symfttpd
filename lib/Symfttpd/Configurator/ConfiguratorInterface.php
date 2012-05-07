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

namespace Symfttpd\Configurator;

use Symfttpd\Project\ProjectInterface;

/**
 * Configurator interface.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */
interface ConfiguratorInterface
{
    /**
     * Configure the project so that it can be launched with symfttpd.
     *
     * @abstract
     * @throw Symfttpd\Configurator\Exception\ConfiguratorException
     * @param \Symfttpd\Project\ProjectInterface
     * @param array $options
     * @return void
     */
    public function configure(ProjectInterface $project, array $options);
}

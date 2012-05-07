<?php
/**
 * Symfony2Configurator class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Configurator;

use Symfttpd\Project\ProjectInterface;

class Symfony2Configurator implements ConfiguratorInterface
{
    /**
     * Configure the project so that it can be launched with symfttpd.
     *
     * @throw Symfttpd\Configurator\Exception\ConfiguratorException
     * @param \Symfttpd\Project\ProjectInterface
     * @param array $options
     * @return void
     */
    public function configure(ProjectInterface $project, array $options)
    {
        // TODO: Implement configure() method.
    }
}

<?php
/**
 * Configurator interface.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Configurator;

interface ConfiguratorInterface
{
    /**
     * Configure the project so that it can be launched with symfttpd.
     *
     * @abstract
     * @throw Symfttpd\Configurator\Exception\ConfiguratorException
     * @param $path
     * @param array $options
     * @return void
     */
    public function configure($path, array $options);
}

<?php
/**
 * Configurator interface.
 * 
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

interface Configurator
{
    /**
     * @abstract
     * @throw Symfttpd\Configurator\Exception\ConfiguratorException
     * @return void
     */
    function configure();
}
<?php
/**
 * SymfonyMaker class.
 * 
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Configurator;

use Symfttpd\Configurator\Symfony2Configurator;
use Symfttpd\Configurator\Symfony14Configurator;

class SymfonyConfigurator implements Configurator
{
    protected $version = '2.0';

    public function __construct($version = '2.0')
    {
        $this->version = $version;
    }

    public function configure()
    {
        switch ($this->version) {
            case '1.4':
                throw new \Exception('symfony 1.4 configurator not implemented yet');
                break;
            case '2.0':
            default:
                $configurator = new Symfony2Configurator();
                break;
        }
    }
}

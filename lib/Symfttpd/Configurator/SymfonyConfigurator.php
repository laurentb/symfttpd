<?php
/**
 * SymfonyMaker class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 25/10/11
 */

namespace Symfttpd\Configurator;

use Symfttpd\Configurator\ConfiguratorInterface;
use Symfttpd\Configurator\Symfony2Configurator;
use Symfttpd\Configurator\Symfony14Configurator;
use Symfttpd\Configurator\Exception\ConfiguratorException;

class SymfonyConfigurator implements ConfiguratorInterface
{
    protected $version = '2.0';

    public function __construct($version = '2.0')
    {
        $this->version = $version;
    }

    /**
     * Configure the project so that symfftpd
     * will be able to launch it.
     *
     * @param string $path
     * @param array $options
     * @return void
     */
    public function configure($path, array $options)
    {
        $configurator = $this->guessConfigurator();
        $configurator->configure($path, $options);
    }

    /**
     * @return Symfony14Configurator|Symfony2Configurator
     */
    public function guessConfigurator()
    {
        switch ($this->version) {
            case '1.4':
                return new Symfony14Configurator();
                break;
            case '2.0':
                return new Symfony2Configurator();
                break;
            default:
                throw new ConfiguratorException(sprintf('The provided version "%s" is not supported yet.', $this->version));
                break;
        }
    }

    /**
     * @param string $version
     * @return void
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}

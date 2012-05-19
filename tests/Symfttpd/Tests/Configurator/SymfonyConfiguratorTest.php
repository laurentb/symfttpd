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

namespace Symfttpd\Tests\Configurator;

use Symfttpd\Configurator\SymfonyConfigurator;
use Symfttpd\Configurator\Exception\ConfiguratorException;

/**
 * SymfonyConfiguratorTest class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 06/11/11
 */
class SymfonyConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->configurator = new SymfonyConfigurator();
    }

    public function testGuessConfigurator()
    {
        $this->assertInstanceOf('Symfttpd\\Configurator\\Symfony2Configurator', $this->configurator->guessConfigurator());
        $this->assertEquals('2', $this->configurator->getVersion());

        $this->configurator->setVersion('1.4');
        $this->assertInstanceOf('Symfttpd\\Configurator\\Symfony14Configurator', $this->configurator->guessConfigurator());
        $this->assertEquals('1.4', $this->configurator->getVersion());
    }

    /**
     * @expectedException Symfttpd\Configurator\Exception\ConfiguratorException
     * @expectedExceptionMessage The provided version "4" is not supported yet.
     * @return void
     */
    public function testGuessConfiguratorException()
    {
        $this->configurator->setVersion('4');
        $this->configurator->guessConfigurator();
    }
}

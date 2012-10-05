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

namespace Symfttpd\Tests\factory;

use Symfttpd\Configuration\ConfigurationFactory;

/**
 * factoryFactoryTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/04/12
 */
class ConfigurationFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;

    public function setUp()
    {
        $this->factory = new ConfigurationFactory();
    }

    public function testCreateProjectConfiguration()
    {
        $configuration = $this->factory->createProjectConfiguration();

        $this->assertInstanceOf('\\Symfttpd\\Configuration\\ProjectConfiguration', $configuration);
    }

    public function testCreateServerConfiguration()
    {
        $configuration = $this->factory->createServerConfiguration();

        $this->assertInstanceOf('\\Symfttpd\\Configuration\\ServerConfiguration', $configuration);
    }
}

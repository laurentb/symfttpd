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

namespace Symfttpd\Tests;

use Symfttpd\Config;
use Symfttpd\Factory;
use Symfttpd\Symfttpd;

/**
 * FactoryTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public $factory;

    public function setUp()
    {
        $this->factory = new Factory();
    }

    /**
     * @covers createSymfttpd
     * @covers createConfig
     * @covers createProject
     */
    public function testCreate()
    {
        $symfttpd = $this->factory->create(array('project_type' => 'php', 'project_version' => null));
        $config   = $symfttpd->getConfig();
        $project  = $symfttpd->getProject();
        $server   = $symfttpd->getServer();

        $this->assertInstanceOf('\\Symfttpd\\Symfttpd', $symfttpd);
        $this->assertInstanceOf('\\Symfttpd\\Config', $config);
        $this->assertInstanceOf('\\Symfttpd\\Project\\ProjectInterface', $project);
        $this->assertInstanceOf('\\Symfttpd\\Server\\ServerInterface', $server);
    }
}

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
use Symfttpd\Symfttpd;

/**
 * SymfttpdTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Symfttpd
     */
    protected $symfttpd;

    public function setUp()
    {
        $this->symfttpd = new Symfttpd(new Config(array()));
    }

    /**
     * @covers \Symfttpd\Symfttpd::getProject
     * @covers \Symfttpd\Symfttpd::setProject
     */
    public function testSetGetProject()
    {
        $this->symfttpd->setProject($this->getMock('\Symfttpd\Project\ProjectInterface'));
        $this->assertInstanceof('\Symfttpd\Project\ProjectInterface', $this->symfttpd->getProject());
    }

    /**
     * @covers \Symfttpd\Symfttpd::getServer
     * @covers \Symfttpd\Symfttpd::setServer
     */
    public function testSetGetServer()
    {
        $this->symfttpd->setServer($this->getMock('\Symfttpd\Server\ServerInterface'));
        $this->assertInstanceof('\Symfttpd\Server\ServerInterface', $this->symfttpd->getServer());
    }

    /**
     * @covers \Symfttpd\Symfttpd::getGenerator
     * @covers \Symfttpd\Symfttpd::setGenerator
     */
    public function testSetGetGenerator()
    {
        $this->symfttpd->setGenerator($this->getMock('\Symfttpd\ConfigurationGenerator', array(), array(), '', false));
        $this->assertInstanceOf('\Symfttpd\ConfigurationGenerator', $this->symfttpd->getGenerator());
    }

    /**
     * @covers \Symfttpd\Symfttpd::getConfig
     * @covers \Symfttpd\Symfttpd::setConfig
     */
    public function testSetGetConfig()
    {
        $this->symfttpd->setConfig(new Config());
        $this->assertInstanceOf('\Symfttpd\Config', $this->symfttpd->getConfig());
    }
}

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
use Symfttpd\Tests\Configuration\MockConfigurationFactory;

/**
 * SymfttpdTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class SymfttpdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfttpd\Symfttpd
     */
    protected $symfttpd;

    public function setUp()
    {
        $this->symfttpd = new Symfttpd(new Config(array()));
    }

    public function testGetProject()
    {
        $factory = new Factory();

        $this->symfttpd = new Symfttpd();
        $this->symfttpd->setConfig(new Config(array(
            'project_type' => 'symfony',
            'project_version' => '1'
        )));
        $this->symfttpd->setProject($factory->createProject($this->symfttpd->getConfig()));

        $project = $this->symfttpd->getProject();

        $this->assertInstanceof('Symfttpd\\Project\\ProjectInterface', $project);
    }

    public function testGetServer()
    {
        $factory = new Factory();

        $this->symfttpd = new Symfttpd();

        $this->symfttpd->setConfig(new Config(array(
            'project_type' => 'symfony',
            'project_version' => '1',
            'server_type' => 'lighttpd'
        )));

        $project = $this->getMock('\\Symfttpd\\Project\\Symfony1', array(), array(new Config()));
        $project->expects($this->any())
            ->method('getLogDir')
            ->will($this->returnValue(sys_get_temp_dir()));

        $project->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue(sys_get_temp_dir()));

        $this->symfttpd->setProject($project);
        $this->symfttpd->setServer($factory->createServer($this->symfttpd->getConfig(), $project));

        $this->assertInstanceof('Symfttpd\\Server\\ServerInterface', $this->symfttpd->getServer());
    }
}

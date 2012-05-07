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

use Symfttpd\Symfttpd;

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
        $this->symfttpd = new Symfttpd($this->getConfigurationMock());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A project type must be set in the symfttpd.conf.php file.
     */
    public function testGetProjectTypeException()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->at(0))
            ->method('has')
            ->with('want')
            ->will($this->returnValue(false));

        $this->symfttpd->getConfiguration()
            ->expects($this->at(1))
            ->method('has')
            ->with('project_type')
            ->will($this->returnValue(false));

        $this->symfttpd->getProjectType();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A project version must be set in the symfttpd.conf.php file.
     */
    public function testGetProjectVersionException()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->at(0))
            ->method('has')
            ->with('want')
            ->will($this->returnValue(false));

        $this->assertEquals('symfony', $this->symfttpd->getProjectVersion());
    }

    public function testGetProjectType()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->at(0))
            ->method('has')
            ->with('want')
            ->will($this->returnValue(false));

        $this->symfttpd->getConfiguration()
            ->expects($this->at(1))
            ->method('has')
            ->with('project_type')
            ->will($this->returnValue(true));

        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('get')
            ->with('project_type', null)
            ->will($this->returnValue('symfony'));

        $this->assertEquals('symfony', $this->symfttpd->getProjectType());
    }

    public function testGetProjectTypeBC()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('has')
            ->with('want')
            ->will($this->returnValue(true));

        $this->assertEquals('symfony', $this->symfttpd->getProjectType());
    }

    public function testGetProjectVersion()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->at(0))
            ->method('has')
            ->with('want')
            ->will($this->returnValue(false));

        $this->symfttpd->getConfiguration()
            ->expects($this->at(1))
            ->method('has')
            ->with('project_version')
            ->will($this->returnValue(true));

        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('get')
            ->with('project_version')
            ->will($this->returnValue('1.4'));

        $this->assertEquals('1.4', $this->symfttpd->getProjectVersion());
    }

    public function testGetProjectVersionBC()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('has')
            ->with('want')
            ->will($this->returnValue(true));

        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('get')
            ->with('want')
            ->will($this->returnValue('1.4'));

        $this->assertEquals('1.4', $this->symfttpd->getProjectVersion());
    }

    public function testGetProject()
    {
        $this->symfttpd = new Symfttpd(new \Symfttpd\Tests\Fixtures\TestConfiguration());
        $this->symfttpd->getConfiguration()->add(array(
            'project_type' => 'symfony',
            'project_version' => '1.4'
        ));

        $project = $this->symfttpd->getProject();

        $this->assertInstanceof('Symfttpd\\Project\\ProjectInterface', $project);
    }

    public function testGetServerTypeBC()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('has')
            ->with('lighttpd_cmd')
            ->will($this->returnValue(true));

        $this->assertEquals('lighttpd', $this->symfttpd->getServerType());
    }

    public function testGetServerType()
    {
        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('has')
            ->with('lighttpd_cmd')
            ->will($this->returnValue(false));

        $this->symfttpd->getConfiguration()
            ->expects($this->once())
            ->method('get')
            ->with('server_type')
            ->will($this->returnValue('foo'));


        $this->assertEquals('foo', $this->symfttpd->getServerType());
    }

    public function testGetServer()
    {
        $this->symfttpd = new Symfttpd(new \Symfttpd\Tests\Fixtures\TestConfiguration());
        $this->symfttpd->getConfiguration()->add(array(
            'project_type' => 'symfony',
            'project_version' => '1.4',
            'server_type' => 'lighttpd'
        ));

        $this->assertInstanceof('Symfttpd\\Server\\ServerInterface', $this->symfttpd->getServer());
    }

    /**
     * Return a SymfttpdConfiguration mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getConfigurationMock()
    {
        $configuration = $this->getMockBuilder('\\Symfttpd\\Configuration\\SymfttpdConfiguration')
            ->disableOriginalConstructor()
            ->getMock();

        return $configuration;
    }
}

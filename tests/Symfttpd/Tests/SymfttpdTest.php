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

    public function testGetServer()
    {
        $this->symfttpd = new Symfttpd(new \Symfttpd\Tests\Fixtures\TestConfiguration());
        $this->symfttpd->getConfiguration()->add(array(
            'project_type' => 'symfony',
            'project_version' => '1.4',
            'server_type' => 'lighttpd'
        ));

        $project = $this->getMock('\Symfttpd\Project\Symfony14');
        $this->symfttpd->setProject($project);

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

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

use Symfttpd\Factory;

/**
 * FactoryTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSymfttpd()
    {
        $symfttpd = Factory::createSymfttpd();

        $this->assertInstanceOf('Symfttpd\\Symfttpd', $symfttpd);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "foo" in version "bar" is not supported.
     */
    public function testCreateProjectException()
    {
        Factory::createProject('foo', 'bar');
    }

    public function testCreateProject()
    {
        $project = Factory::createProject('symfony', '1.4');

        $this->assertInstanceof('Symfttpd\\Project\\ProjectInterface', $project);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "foo" is not supported.
     */
    public function testCreateServerException()
    {
        Factory::createServer('foo', $this->getMock('\Symfttpd\Project\Symfony14'));
    }

    public function testCreateServer()
    {
        $project = $this->getMock('\Symfttpd\Project\Symfony14');

        $server = Factory::createServer('lighttpd', $project);

        $this->assertInstanceof('Symfttpd\\Server\\ServerInterface', $server);
    }
}

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
     * @covers \Symfttpd\Factory::create
     * @covers \Symfttpd\Factory::createSymfttpd
     * @covers \Symfttpd\Factory::createConfig
     * @covers \Symfttpd\Factory::createProject
     * @covers \Symfttpd\Factory::createServerGenerator
     * @covers \Symfttpd\Symfttpd::setServerGenerator
     * @covers \Symfttpd\Symfttpd::getServerGenerator
     */
    public function testCreate()
    {
        $symfttpd = $this->factory->create(array('project_type' => 'php', 'project_version' => null));
        $config   = $symfttpd->getConfig();
        $project  = $symfttpd->getProject();
        $server   = $symfttpd->getServer();
        $generator = $symfttpd->getServerGenerator();

        $this->assertInstanceOf('\\Symfttpd\\Symfttpd', $symfttpd);
        $this->assertInstanceOf('\\Symfttpd\\Config', $config);
        $this->assertInstanceOf('\\Symfttpd\\Project\\ProjectInterface', $project);
        $this->assertInstanceOf('\\Symfttpd\\Server\\ServerInterface', $server);
        $this->assertInstanceOf('\\Symfttpd\\Server\\Generator\\GeneratorInterface', $generator);
    }

    public function testCreateProject()
    {
        $config = new Config(array('want' => '1.4'));

        $project = $this->factory->createProject($config);

        $this->assertInstanceOf('\\Symfttpd\\Project\\Symfony1', $project);
        $this->assertEquals('1', $project->getVersion());
        $this->assertEquals('symfony', $project->getName());
    }

    /**
     * @dataProvider getInvalidConfig
     * @param $config
     */
    public function testCreateProjectException($config, $exception, $exceptionMessage)
    {
        $this->setExpectedException($exception, $exceptionMessage);
        $this->factory->createProject($config);
    }

    public function getInvalidConfig()
    {
        return array(
            array(
                'config' => new Config(array('project_type' => 'foo')),
                'exception' => '\\RuntimeException',
                'exceptionMessage' => 'A project version must be set in the symfttpd.conf.php file.'
            ),
            array(
                'config' => new Config(array('project_type' => 'foo', 'project_version' => 3)),
                'exception' => '\\InvalidArgumentException',
                'exceptionMessage' => '"foo" in version "3" is not supported.'
            )
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "foo" is not supported
     */
    public function testCreateServerException()
    {
        $config = new Config(array('server_type' => 'foo'));

        $this->factory->createServer(
            $config,
            $this->getMock('\\Symfttpd\\Project\\ProjectInterface')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage "foo" is not supported
     */
    public function testCreateServerGeneratorException()
    {
        $config = new Config(array('server_type' => 'foo'));

        $this->factory->createServerGenerator(
            $config,
            $this->getMock('\\Symfttpd\\Server\\ServerInterface'),
            $this->getMock('\\Symfttpd\\Project\\ProjectInterface')
        );
    }
}

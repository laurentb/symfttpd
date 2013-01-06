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

namespace Symfttpd\Tests\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfttpd\Console\Command\GenconfCommand;

/**
 * GenconfCommand test class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class GenconfCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\Filesystem\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @var Symfony\Component\Console\Tester\CommandTester $command
     */
    protected $command;

    public function setUp()
    {
        $this->fixtures = sys_get_temp_dir() . '/symfttpd-test';

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir(
            array(
                $this->fixtures . '/cache/lighttpd/',
                $this->fixtures . '/log/lighttpd/',
                $this->fixtures . '/web',
            )
        );

        $this->command = new GenconfCommand();
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->fixtures);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The path "/foo" does not exist
     */
    public function testExecuteException()
    {
        $application = new \Symfttpd\Console\Application();
        $application->add($this->command);
        $tester = new CommandTester($this->command);
        $tester->execute(array(
            'command' => $this->command->getName(),
            '--path' => '/foo'
        ));
    }

    public function testExecuteDumpTheConfiguration()
    {
        $path = $this->fixtures . '/web';
        $container = $this->getContainer($path);

        $container['generator']->expects($this->once())
            ->method('dump')
            ->with($this->isInstanceOf('\Symfttpd\Server\ServerInterface'));

        $application = new \Symfttpd\Console\Application();
        $application->setContainer($container);
        $application->add($this->command);

        $tester = new CommandTester($this->command);
        $tester->execute(array(
            'command'  => 'genconf',
            '--path'   => $path
        ));

        $this->assertContains('The configuration file has been well generated.', $tester->getDisplay());
    }

    public function testExecutePrintTheConfiguration()
    {
        $path = $this->fixtures . '/web';
        $container = $this->getContainer($path);

        $container['generator']->expects($this->once())
            ->method('generate')
            ->with($this->isInstanceOf('\Symfttpd\Server\ServerInterface'))
            ->will($this->returnValue('foo'));

        $application = new \Symfttpd\Console\Application();
        $application->setContainer($container);
        $application->add($this->command);

        $tester = new CommandTester($this->command);
        $tester->execute(array(
            'command'  => 'genconf',
            '--path'   => $path,
            '--output' => true
        ));

        $this->assertEquals('foo', $tester->getDisplay());
    }

    public function getContainer($path)
    {
        $container = new \Pimple();

        $container['generator'] = $this->getMock('\Symfttpd\ConfigurationGenerator', array(), array(), '', false);

        $container['project'] = $this->getMock('\Symfttpd\Project\ProjectInterface', array(), array(), '', false);
        $container['project']->expects($this->once())
            ->method('setRootDir')
            ->with($path);

        $container['server'] = $this->getMock('\Symfttpd\Server\ServerInterface');
        $container['server']->expects($this->once())
            ->method('bind')
            ->with($this->equalTo('127.0.0.1'), $this->equalTo('4042'));

        return $container;
    }
}

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

use Symfttpd\Command\GenconfCommand;
use Symfttpd\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * GenconfCommand test class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class GenconfCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfttpd\Filesystem\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @var Symfony\Component\Console\Tester\CommandTester $command
     */
    protected $command;

    public function setUp()
    {
        $this->fixtures = sys_get_temp_dir().'/symfttpd-test';

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir(array(
            $this->fixtures.'/cache/lighttpd/',
            $this->fixtures.'/log/lighttpd/',
            $this->fixtures.'/web',
        ));

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
        $tester = new CommandTester($this->command);
        $tester->execute(array('type' => 'all', '--path' => '/foo'), array('interactive' => false));
    }

    /**
     * @dataProvider getExecutionType
     * @param $type
     */
    public function testExecuteWrite($type, $generateMethod, $output)
    {
        $symfttpd = $this->getSymfttpd();

        $server = $this->getMockBuilder('\\Symfttpd\\Server\\Lighttpd')
            ->setMethods(array($generateMethod, 'write', 'getProject', 'getConfigFilename', 'getRulesFilename', 'getCacheDir'))
            ->setConstructorArgs(array(
                $symfttpd['project'],
                $symfttpd['twig'],
                $symfttpd['loader'],
                $symfttpd['writer'],
                new \Symfttpd\OptionBag())
            )
            ->getMock();

        if ($type !== 'rules') {
            $server->expects($this->once())
                ->method('getConfigFilename')
                ->will($this->returnValue('lighttpd.conf'));

            $server->expects($this->once())
                ->method($generateMethod)
                ->with($symfttpd['configuration']);
        }

        if ($type !== 'config') {
            $server->expects($this->once())
                ->method('getRulesFilename')
                ->will($this->returnValue('rules.conf'));

            $server->expects($this->once())
                ->method($generateMethod);
        }

        $server->expects($this->atLeastOnce())
            ->method('getCacheDir');

        $server->expects($this->once())
            ->method('write')
            ->with($this->equalTo($type));

        $symfttpd['server']  = $server;

        $application = new \Symfttpd\Console\Application();
        $application->setAutoExit(false);
        $application->setSymfttpd($symfttpd);
        $application->add($this->command);

        $tester = new ApplicationTester($application);
        $tester->run(array('command' => 'genconf', 'type' => $type, '--path' => $this->fixtures.'/web'), array('interactive' => false));

        $this->assertContains($output, $tester->getDisplay());
        $this->assertContains('The configuration file has been well generated.', $tester->getDisplay());
    }

    public function getExecutionType()
    {
        return array(
            array('all', 'generate', 'Generate lighttpd.conf and rules.conf'),
            array('config', 'generateConfiguration', 'Generate lighttpd.conf'),
            array('rules', 'generateRules', 'Generate rules.conf'),
        );
    }

    /**
     * @dataProvider getExecutionReadType
     * @param $type
     */
    public function testExecuteRead($type, $generateMethod, $readMethod)
    {
        $symfttpd = $this->getSymfttpd();

        $server = $this->getMockBuilder('\\Symfttpd\\Server\\Lighttpd')
            ->setMethods(array($generateMethod, $readMethod, 'getProject', 'getConfigFilename', 'getRulesFilename', 'getCacheDir'))
            ->setConstructorArgs(array(
                $symfttpd['project'],
                $symfttpd['twig'],
                $symfttpd['loader'],
                $symfttpd['writer'],
                new \Symfttpd\OptionBag())
            )
            ->getMock();

        if ($type !== 'rules') {
            $server->expects($this->once())
                ->method('getConfigFilename')
                ->will($this->returnValue('lighttpd.conf'));

            $server->expects($this->once())
                ->method($generateMethod)
                ->with($symfttpd['configuration']);
        }

        if ($type !== 'config') {
            $server->expects($this->once())
                ->method('getRulesFilename')
                ->will($this->returnValue('rules.conf'));

            $server->expects($this->once())
                ->method($generateMethod);
        }

        $server->expects($this->atLeastOnce())
            ->method('getCacheDir');

        $server->expects($this->once())
            ->method($readMethod);

        $symfttpd['server']  = $server;

        $application = new \Symfttpd\Console\Application();
        $application->setAutoExit(false);
        $application->setSymfttpd($symfttpd);
        $application->add($this->command);

        $tester = new ApplicationTester($application);
        $tester->run(array('command' => 'genconf', 'type' => $type, '--path' => $this->fixtures.'/web', '--output' => true), array('interactive' => false));

        $this->assertEmpty($tester->getDisplay());
    }

    public function getExecutionReadType()
    {
        return array(
            array('all', 'generate', 'read'),
            array('config', 'generateConfiguration', 'readConfiguration'),
            array('rules', 'generateRules', 'readRules'),
        );
    }

    public function getSymfttpd()
    {
        $config = $this->getMock('\\Symfttpd\\Configuration\\SymfttpdConfiguration');
        $symfttpd = new \Symfttpd\Symfttpd($config);

        $twig_loader = $this->getMock('\\Twig_Loader_Filesystem', array('addPath'), array(''));
        $twig_loader->expects($this->once())
            ->method('addPath');

        $twig = $this->getMock('\\Twig_Environment', array('getLoader'), array($twig_loader));
        $twig->expects($this->once())
            ->method('getLoader')
            ->will($this->returnValue($twig_loader));

        $project = $this->getMockForAbstractClass('\\Symfttpd\\Project\\BaseProject', array('getProjectType', 'getProjectVersion'), '', false);
        $loader = $this->getMock('\\Symfttpd\\Loader');
        $writer = $this->getMock('\\Symfttpd\\Writer');

        $symfttpd['twig']  = $twig;
        $symfttpd['project']  = $project;
        $symfttpd['loader']  = $loader;
        $symfttpd['writer']  = $writer;

        return $symfttpd;
    }
}

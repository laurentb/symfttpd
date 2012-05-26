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

use Symfttpd\Command\SpawnCommand;
use Symfttpd\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * SpawnCommand test class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SpawnCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfttpd\Filesystem\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @var Symfony\Component\Console\Tester\CommandTester $command
     */
    protected $command;

    /**
     * Path to the fixtures.
     *
     * @var string
     */
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = sys_get_temp_dir().'/symfttpd-test';

        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir(array(
            $this->fixtures.'/cache/lighttpd/',
            $this->fixtures.'/log/lighttpd/',
            $this->fixtures.'/web',
        ));

        $this->command = new SpawnCommand();
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->fixtures);
    }

    public function testExecute()
    {
        $this->markTestSkipped('Spawn and watch methods make it untestable... yet.');

        $symfttpd = new \Symfttpd\Symfttpd($this->getConfiguration());

        $symfttpd['server'] = $this->getServer();

        $application = new \Symfttpd\Console\Application();
        $application->setAutoExit(false);
        $application->setSymfttpd($symfttpd);
        $application->add($this->command);

        $process = $this->getProcess();

        $symfttpd['server']->expects($this->once())
            ->method('start')
            ->will($this->returnValue($process));

        $tester = new ApplicationTester($application);
        $tester->run(array('command' => 'spawn'), array('interactive' => false));

        $this->assertTrue($process->isRunning());
    }

    public function getServer()
    {
        $twig_loader = $this->getMock('\\Twig_Loader_Filesystem', array('addPath'), array(''));
        $twig_loader->expects($this->once())
            ->method('addPath');

        $twig = $this->getMock('\\Twig_Environment', array('getLoader', 'render'), array($twig_loader));
        $twig->expects($this->once())
            ->method('getLoader')
            ->will($this->returnValue($twig_loader));

        $twig->expects($this->atLeastOnce())
            ->method('render')
            ->will($this->returnValue(''));

        $project = $this->getProject();

        $loader = $this->getMock('\\Symfttpd\\Loader');
        $writer = $this->getMock('\\Symfttpd\\Writer');

        $server = $this->getMockBuilder('\\Symfttpd\\Server\\Lighttpd')
            ->setMethods(array('write', 'getProject', 'getConfigFilename', 'getRulesFilename', 'getCacheDir', 'start'))
            ->setConstructorArgs(array(
                $project,
                $twig,
                $loader,
                $writer,
                new \Symfttpd\OptionBag())
            )
            ->getMock();

        $server->expects($this->once())
            ->method('getProject')
            ->will($this->returnValue($project));

        $server->expects($this->once())
            ->method('getConfigFilename')
            ->will($this->returnValue('lighttpd.conf'));

        $server->expects($this->once())
            ->method('getRulesFilename')
            ->will($this->returnValue('rules.conf'));

        $server->expects($this->atLeastOnce())
            ->method('getCacheDir');

        return $server;
    }

    public function getProject()
    {
        $project = $this->getMockBuilder('\\Symfttpd\\Project\\Php')
            ->setMethods(array())
            ->setConstructorArgs(array(new \Symfttpd\OptionBag(), $this->fixtures))
            ->getMockForAbstractClass();

        return $project;
    }

    public function getConfiguration()
    {
        return $this->getMock('\\Symfttpd\\Configuration\\SymfttpdConfiguration');
    }

    public function getProcess()
    {
        $process = $this->getMockBuilder('\\Symfony\\Component\\Console\\Process')
            ->disableOriginalConstructor()
            ->setMethods(array('run'))
            ->getMock();

        $process->expects($this->once())
            ->method('run')
            ->will($this->returnValue(0));

        return $process;
    }
}

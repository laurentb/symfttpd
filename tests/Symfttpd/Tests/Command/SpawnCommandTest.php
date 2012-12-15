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

use Symfony\Component\Console\Tester\CommandTester;
use Symfttpd\Server\MockServer;
use Symfttpd\Command\SpawnCommand;

/**
 * SpawnCommand test class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SpawnCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\Console\Tester\CommandTester $command
     */
    protected $command;

    public function setUp()
    {
        $this->command = new SpawnCommand();
    }

    /**
     * @covers \Symfttpd\Command\SpawnCommand::execute
     * @covers \Symfttpd\Command\SpawnCommand::getMessage
     */
    public function testExecute()
    {
        $symfttpd = $this->getSymfttpd();
        $symfttpd->expects($this->any())
            ->method('getServer')
            ->will($this->returnValue($this->getServer()));

        $this->command->setSymfttpd($symfttpd);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('-p' => 4043));

        $this->assertRegExp('/symfttpd started on 127.0.0.1, port 4043./', $commandTester->getDisplay());
        $this->assertRegExp('#http://127\.0\.0\.1:4043/index.php#', $commandTester->getDisplay());
    }

    /**
     * @covers \Symfttpd\Command\SpawnCommand::execute
     * @covers \Symfttpd\Command\SpawnCommand::getMessage
     */
    public function testExecuteOnAllInterfaces()
    {
        $server = new MockServer();
        $server->setExecutableFiles(array('index.php'));

        $symfttpd = $this->getSymfttpd();
        $symfttpd->expects($this->any())
            ->method('getServer')
            ->will($this->returnValue($server));

        $this->command->setSymfttpd($symfttpd);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(array('-p' => 4043, '--all' => true));

        $this->assertRegExp('/started on all interfaces, port 4043./', $commandTester->getDisplay());
        $this->assertRegExp('#http://localhost:4043/index.php#', $commandTester->getDisplay());
    }

    public function getSymfttpd()
    {
        $symfttpd = $this->getMock('\Symfttpd\Symfttpd');
        $symfttpd->expects($this->once())
            ->method('getGenerator')
            ->will($this->returnValue($this->getMock('\Symfttpd\ConfigurationGenerator', array(), array(), '', false)));

        return $symfttpd;
    }

    public function getServer()
    {
        $server = $this->getMock('\Symfttpd\Server\ServerInterface');

        $server->expects($this->any())
            ->method('getAddress')
            ->will($this->returnValue('127.0.0.1'));

        $server->expects($this->any())
            ->method('getPort')
            ->will($this->returnValue('4043'));

        $server->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('symfttpd'));

        $server->expects($this->any())
            ->method('getExecutableFiles')
            ->will($this->returnValue(array('index.php')));

        $server->expects($this->any())
            ->method('start')
            ->will($this->returnValue(1));

        return $server;
    }

    public function testGetMessage()
    {
        $server = $this->getMock('\Symfttpd\Server\ServerInterface');

        $server->expects($this->exactly(3))
            ->method('getAddress')
            ->will($this->returnValue('localhost'));

        $server->expects($this->exactly(3))
            ->method('getPort')
            ->will($this->returnValue('4042'));

        $server->expects($this->once())
            ->method('getExecutableFiles')
            ->will($this->returnValue(array('app.php', 'app_dev.php')));

        $command = new SpawnCommand();
        $message = $command->getMessage($server);

        $this->assertRegExp('~localhost:4042~', $message);
    }
}

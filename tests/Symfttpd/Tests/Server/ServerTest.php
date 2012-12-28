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

namespace Symfttpd\Tests\Server;

use Symfttpd\Server\Server;
use Symfttpd\Config;

/**
 * ServerTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class ServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $type
     * @param $support
     * @covers \Symfttpd\Server\Server::isSupported
     * @dataProvider getTypeSupport
     */
    public function testServerSupportsType($type, $support)
    {
        $server = new Server();
        $this->assertEquals($support, $server->isSupported($type));
    }

    public function getTypeSupport()
    {
        return array(
            array('lighttpd', true),
            array('foo', false),
            array(null, false),
        );
    }

    /**
     * @covers \Symfttpd\Server\Server::configure
     */
    public function testConfigureTheServer()
    {
        $server = new Server();
        $config = new Config(array('server_type' => 'lighttpd'));
        $project = $this->getMock('\Symfttpd\Project\ProjectInterface');

        try {
            $server->configure($config, $project);
        } catch (\RuntimeException $e) {
            $this->fail($e->getMessage());
        }

        $this->assertEquals($config->get('server_type'), $server->getType());
    }

    /**
     * @param $type
     * @covers \Symfttpd\Server\Server::configure
     * @dataProvider getUnsupportedType
     */
    public function testConfigureTheServerWithUnsupportedServerTypeThrowsARuntimeException($type)
    {
        $this->setExpectedException('\RuntimeException', "The provided type of server ($type) is not supported, only lighttpd or nginx are available.");

        $server = new Server();
        $server->configure(new Config(array('server_type' => $type)), $this->getMock('\Symfttpd\Project\ProjectInterface'));
    }

    public function getUnsupportedType()
    {
        return array(
            array('foo'),
            array(null),
        );
    }

    public function testStartTheServer()
    {
        $process = $this->getMock('\Symfony\Component\Process\Process', array(), array(null));
        $process->expects($this->once())
            ->method('run')
            ->will($this->returnValue(0));

        $processBuilder = $this->getMock('\Symfony\Component\Process\ProcessBuilder');
        $processBuilder->expects($this->once())
            ->method('setArguments')
            ->with($this->isType('array'))
            ->will($this->returnSelf());
        $processBuilder->expects($this->once())
            ->method('getProcess')
            ->will($this->returnValue($process));

        $server = new Server();
        $server->setType('lighttpd');
        $server->setProcessBuilder($processBuilder);

        $server->start($this->getMock('\Symfttpd\ConfigurationGenerator', array('dump'), array(), '', false));
    }
}

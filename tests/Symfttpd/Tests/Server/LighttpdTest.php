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

use Symfttpd\Server\Lighttpd;

/**
 * LighttpdTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Server\Lighttpd
     */
    protected $server;

    public function setUp()
    {
        $this->server = new Lighttpd();
    }

    public function testGetCommand()
    {
        $this->server->setCommand('/opt/local/sbin/lighttpd');
        $this->assertEquals('/opt/local/sbin/lighttpd', $this->server->getCommand());
    }

    public function testConfigure()
    {
        $config = new \Symfttpd\Config(array(
            'symfttpd_dir'      => sys_get_temp_dir(),
            'server_log_dir'    => 'foo/log',
            'server_error_log'  => 'error.log',
            'server_access_log' => 'access.log',
            'server_pidfile'    => 'foo.pid',
        ));

        $project = $this->getMock('\Symfttpd\Project\ProjectInterface');
        $project->expects($this->any())
            ->method('getLogDir')
            ->will($this->returnValue('foo/cache'));

        $project->expects($this->once())
            ->method('getWebDir')
            ->will($this->returnValue('foo/web'));

        $project->expects($this->once())
            ->method('getIndexFile')
            ->will($this->returnValue('index.php'));

        $project->expects($this->once())
            ->method('getDefaultReadableDirs')
            ->will($this->returnValue(array()));

        $project->expects($this->once())
            ->method('getDefaultReadableFiles')
            ->will($this->returnValue(array()));

        $project->expects($this->once())
            ->method('getDefaultExecutableFiles')
            ->will($this->returnValue(array()));

        $this->server->configure($config, $project);

        $this->assertEquals('foo/log/error.log', $this->server->getErrorLog());
        $this->assertEquals('foo/log/access.log', $this->server->getAccessLog());
        $this->assertEquals(sys_get_temp_dir().'/foo.pid', $this->server->getPidfile());
        $this->assertEquals('index.php', $this->server->getIndexFile());
        $this->assertEquals('foo/web', $this->server->getDocumentRoot());
        $this->assertEquals(array(), $this->server->getAllowedDirs());
        $this->assertEquals(array(), $this->server->getAllowedFiles());
        $this->assertEquals(array(), $this->server->getExecutableFiles());
        $this->assertEquals(array(), $this->server->getUnexecutableDirs());
    }
}

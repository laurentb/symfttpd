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
        $finder = $this->getMock('\\Symfony\\Component\\Process\\ExecutableFinder');
        $finder->expects($this->once())
            ->method('find')
            ->with('lighttpd')
            ->will($this->returnValue('/usr/sbin/lighttpd'));

        $this->assertEquals('/usr/sbin/lighttpd', $this->server->getCommand($finder));

        $this->server->setCommand('/opt/local/sbin/lighttpd');
        $this->assertEquals('/opt/local/sbin/lighttpd', $this->server->getCommand());
    }

    /**
     * @expectedException \Symfttpd\Exception\ExecutableNotFoundException
     * @expectedExceptionMessage lighttpd executable not found.
     */
    public function testGetCommandException()
    {
        $finder = $this->getMock('\\Symfony\\Component\\Process\\ExecutableFinder');
        $finder->expects($this->once())
            ->method('find')
            ->with('lighttpd')
            ->will($this->returnValue(null));

        $this->server->getCommand($finder);
    }
}

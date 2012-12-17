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

namespace Symfttpd\Tests\Gateway;

use Symfttpd\Tests\Mock\MockGateway;
use Symfttpd\Config;

/**
 * PhpFpmTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class GatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Gateway\PhpFpm
     */
    protected $gateway;

    public function setUp()
    {
        $this->gateway = new MockGateway();
    }

    /**
     * @testdox should configure the gateway
     */
    public function testConfigure()
    {
        $this->gateway->configure(new Config(array('gateway_cmd' => '/usr/bin/php-fpm')));
        $this->assertEquals('/usr/bin/php-fpm', $this->gateway->getCommand());
    }

    /**
     * @testdox should change the command
     */
    public function testSetGetCommand()
    {
        $this->gateway->setCommand('foo');
        $this->assertEquals('foo', $this->gateway->getCommand());
    }

    /**
     * @testdox should change the socket
     */
    public function testSetGetSocket()
    {
        $this->gateway->setSocket('foo');
        $this->assertEquals('foo', $this->gateway->getSocket());
    }
}

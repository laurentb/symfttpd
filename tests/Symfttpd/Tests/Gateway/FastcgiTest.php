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

use Symfttpd\Gateway\Fastcgi;

/**
 * FastcgiTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class FastcgiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Gateway\Fastcgi
     */
    protected $gateway;

    public function setUp()
    {
        $this->gateway = new Fastcgi();
    }

    /**
     * @testdox should configure the gateway
     */
    public function testConfigure()
    {
        $this->assertEquals('fastcgi', $this->gateway->getName());
    }
}

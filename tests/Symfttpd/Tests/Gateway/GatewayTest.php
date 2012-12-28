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
    public function testConfigure()
    {
        $gateway = new MockGateway();
        $gateway->configure(new Config(array('gateway_cmd' => '/usr/bin/php-fpm')));
        $this->assertEquals('/usr/bin/php-fpm', $gateway->getExecutable());
    }
}

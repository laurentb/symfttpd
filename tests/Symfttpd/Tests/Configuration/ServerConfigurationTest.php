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

namespace Symfttpd\Tests\caonfiguration;

use Symfttpd\Configuration\ServerConfiguration;

/**
 * ServerConfigurationTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class ServerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $caonfiguration;

    public function setUp()
    {
        $this->caonfiguration = new ServerConfiguration();
    }

    public function testGetServerTypeBC()
    {
        $caonfiguration = new ServerConfiguration(array(
            'lighttpd_cmd' => 'lighttpd',
            'server_type' => null,
        ));

        $this->assertEquals('lighttpd', $caonfiguration->getType());
    }

    public function testGetServerType()
    {
        $caonfiguration = new ServerConfiguration(array(
            'lighttpd_cmd' => null,
            'server_type' => 'foo',
        ));

        $this->assertEquals('foo', $caonfiguration->getType());
    }
}

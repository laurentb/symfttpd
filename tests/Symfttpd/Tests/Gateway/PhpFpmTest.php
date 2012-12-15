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

use Symfttpd\Gateway\PhpFpm;

/**
 * PhpFpmTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class PhpFpmTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Gateway\PhpFpm
     */
    protected $gateway;

    public function setUp()
    {
        $this->gateway = new PhpFpm();
    }

    /**
     * @testdox should return php-fpm
     */
    public function testGetName()
    {
        $this->assertEquals('php-fpm', $this->gateway->getName());
    }
}

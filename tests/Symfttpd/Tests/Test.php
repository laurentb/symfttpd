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

namespace Symfttpd\Tests;

/**
 * Test class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/10/11
 */
class Test extends \PHPUnit_Framework_TestCase
{
    protected $fixtures;

    public function setUp()
    {
        $this->fixtures = sys_get_temp_dir();
    }
}

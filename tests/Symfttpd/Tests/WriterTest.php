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

namespace Symfttpd\Test;

use Symfttpd\Writer;

/**
 * WriterTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class WriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getDatas
     */
    public function testWrite($content, $file, $force)
    {
        $writer = new Writer();

        $writer->write($content, $file, $force);

        $this->assertTrue(file_exists($file));
        $this->assertEquals($content, file_get_contents($file));
    }

    public function getDatas()
    {
        return array(
            array('some content', sys_get_temp_dir().'/file', true),
            array('some content', sys_get_temp_dir().'/file', false),
        );
    }
}

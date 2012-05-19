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

use Symfttpd\Loader;

/**
 * LoaderTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $content = 'some content';
        $file = sys_get_temp_dir().'/file';

        $writer = new \Symfttpd\Writer();
        $writer->write($content, $file);

        $loader = new Loader();
        $loader->load($file);

        $this->assertEquals($content, file_get_contents($file));
    }

    /**
     * @expectedException \Symfttpd\Exception\LoaderException
     * @expectedExcepionMessage The file "foo" does not exist.
     */
    public function testLoadException()
    {
        $loader = new Loader();
        $loader->load('foo');
    }
}

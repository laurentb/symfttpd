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

namespace Symfttpd\Tests\Watcher;

use Symfttpd\Watcher\Watcher;

/**
 * WatcherTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class WatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testTrackResource()
    {
        $resource = __DIR__;

        $watcher = new Watcher();

        $this->assertFalse($watcher->isTracked($resource));

        $watcher->track($resource, function () {});

        $this->assertTrue($watcher->isTracked($resource));
    }

    public function testTriggerTheCallbackWhenTheResourceChange()
    {
        $fileResource = $this->getMock('\Symfttpd\Watcher\Resource\ResourceInterface');
        $fileResource->expects($this->any())
            ->method('getResource')
            ->will($this->returnValue('/foo/bar.txt'));
        $fileResource->expects($this->exactly(2))
            ->method('hasChanged')
            ->will($this->returnValue(true));

        $test = $this;
        $called = false;

        $watcher = new Watcher();
        $watcher->track($fileResource, function ($resource) use ($test, $fileResource, &$called) {
            $test->assertEquals($resource->getResource(), $fileResource->getResource());
            $called = true;
        });

        $watcher->start(1, 1);

        $this->assertTrue($called, 'The callback should have been called.');
    }
}

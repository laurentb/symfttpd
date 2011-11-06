<?php
/**
 * ConfigurationFinderTest class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 05/11/11
 */

namespace Symfttpd\Tests\Finder;

use Symfttpd\Finder\ConfigurationFinder;

class ConfigurationFinderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->finder = new ConfigurationFinder();
    }

    public function testFind()
    {
        $options = $this->finder->find();

        $this->assertTrue(count($options) > 0);
        $this->assertTrue(isset($options['path'], $options['genconf']));
    }

    public function testAddPath()
    {

        $this->assertEquals(3, count($this->finder->getPaths()));

        $this->finder->addPath(__DIR__.'/../../fixtures/');

        $this->assertEquals(4, count($this->finder->getPaths()));
    }
}

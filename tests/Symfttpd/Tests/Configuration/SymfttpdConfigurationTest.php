<?php
/**
 * SymfttpdConfigurationTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/04/12
 */

namespace Symfttpd\Tests\Configuration;

use Symfttpd\Configuration\SymfttpdConfiguration;

class SymfttpdConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;

    public function setUp()
    {
        $this->configuration = new SymfttpdConfiguration();
        $this->configuration->read();
    }

    public function testAll()
    {
        $this->assertEquals(16, count($this->configuration->all()));
    }

    public function testGet()
    {
        $this->assertEquals('config/lighttpd.php', $this->configuration->get('genconf'));
        $this->assertEquals('test', $this->configuration->get('unexistentvar', 'test'));
        $this->assertNull($this->configuration->get('unexistentvar'));
    }

    public function testHas()
    {
        $this->assertTrue($this->configuration->has('genconf'));
        $this->assertFalse($this->configuration->has('unexistentvar'));
    }

    public function testSet()
    {
        $this->configuration->set('genconf', 'plop');
        $this->assertEquals('plop', $this->configuration->get('genconf'));
        $this->configuration->set('unexistent', 'plop');
        $this->assertEquals('plop', $this->configuration->get('unexistent'));
    }

    public function testRead()
    {
        $configuration = new SymfttpdConfiguration();

        $this->assertEquals(0, count($configuration->all()));

        $configuration->read();

        $this->assertGreaterThan(0, count($configuration->all()));
        $this->assertTrue($configuration->has('path'));
        $this->assertTrue($configuration->has('genconf'));
    }

    public function testAddPath()
    {
        $this->assertEquals(3, count($this->configuration->getPaths()));

        $this->configuration->addPath(__DIR__.'/../../fixtures/');

        $this->assertEquals(4, count($this->configuration->getPaths()));

        $this->configuration->addPath(__DIR__.'/../../fixtures/');

        $this->assertEquals(4, count($this->configuration->getPaths()));
    }

    public function testSetPath()
    {
        $this->configuration->setPaths(array('fixtures', 'home'));
        $this->assertContains('fixtures', $this->configuration->getPaths());
        $this->assertContains('home', $this->configuration->getPaths());
        $this->assertEquals(2, count($this->configuration->getPaths()));
    }
}

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

use Symfttpd\Config;

/**
 * ConfigTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 01/05/12
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $config;

    public function setUp()
    {
        $this->config = new Config();
    }

    /**
     *
     */
    public function testGetIterator()
    {
        $this->assertInstanceOf('ArrayIterator', $this->config->getIterator());
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testAll($config)
    {
        $this->config->add($config);
        $this->assertEquals($config, $this->config->all());
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testGet($config)
    {
        $this->config->add($config);
        $this->assertEquals(reset($config), $this->config->get(key($config)));
    }

    public function testHas()
    {
        $this->config->set('foo', 'bar');
        $this->assertTrue($this->config->has('foo'));
        $this->assertFalse($this->config->has('bar'));

        $this->config->set('foo', null);
        $this->assertFalse($this->config->has('foo'));
        $this->assertFalse($this->config->has('bar'));
    }

    /**
     * @dataProvider getOptions
     */
    public function testSet($name, $value)
    {
        $this->config->set($name, $value);

        $this->assertEquals($value, $this->config->get($name));
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testAdd($config)
    {
        $this->config->add($config);

        // assertArrayHasKey does not work with empty arrays.
        if (!empty($config)) {
            // assert that foo is a key of the config for instance.
            $this->assertArrayHasKey(key($config), $this->config->all());
        }

        $this->assertEquals(count($config), count($this->config->all()));

        // assert that array('foo', 'bar') is the value of $config['bar']
        $this->assertEquals(reset($config), $this->config->get(key($config)));
    }

    public function testMerge()
    {
        $this->config->add(array('foo' => 'bar', 'bar' => 'foo'));
        $this->config->merge(array('foo' => 'foo', 'test' => 'bar'));

        $this->assertEquals(array(
            'foo' => 'foo',
            'bar' => 'foo',
            'test' => 'bar',
        ), $this->config->all());
    }

    public function getOptions()
    {
        return array(
            array('foo', 'bar'),
            array('bar', array('foo', 'bar'))
        );
    }

    public function getArrayOptions()
    {
        return array(
            array(array()),
            array(array('1' => null)),
            array(array('foo' => 'bar')),
            array(array('bar' => array('foo', 'bar')))
        );
    }
}

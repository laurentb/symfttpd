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

namespace Symfttpd\Tests\Configuration;

use Symfttpd\Configuration\OptionBag;

/**
 * OptionBagTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 01/05/12
 */
class OptionBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionBag
     */
    protected $options;

    public function setUp()
    {
        $this->options = new OptionBag();
    }

    /**
     *
     */
    public function testGetIterator()
    {
        $this->assertInstanceOf('ArrayIterator', $this->options->getIterator());
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testAll($options)
    {
        $this->options->add($options);
        $this->assertEquals($options, $this->options->all());
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testGet($options)
    {
        $this->options->add($options);
        $this->assertEquals(reset($options), $this->options->get(key($options)));
    }

    public function testHas()
    {
        $this->options->set('foo', 'bar');
        $this->assertTrue($this->options->has('foo'));
        $this->assertFalse($this->options->has('bar'));

        $this->options->set('foo', null);
        $this->assertFalse($this->options->has('foo'));
        $this->assertFalse($this->options->has('bar'));
    }

    /**
     * @dataProvider getOptions
     */
    public function testSet($name, $value)
    {
        $this->options->set($name, $value);

        $this->assertEquals($value, $this->options->get($name));
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testAdd($options)
    {
        $this->options->add($options);

        // assertArrayHasKey does not work with empty arrays.
        if (!empty($options)) {
            // assert that foo is a key of the options for instance.
            $this->assertArrayHasKey(key($options), $this->options->all());
        }

        $this->assertEquals(count($options), count($this->options->all()));

        // assert that array('foo', 'bar') is the value of $options['bar']
        $this->assertEquals(reset($options), $this->options->get(key($options)));
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

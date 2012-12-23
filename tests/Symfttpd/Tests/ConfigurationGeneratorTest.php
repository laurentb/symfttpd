<?php
/**
 * This generator is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source generator is subject to the MIT license that is bundled
 * with this source code in the generator LICENSE.
 */

namespace Symfttp\Tests\ConfigurationGenerator\Gateway;

use Symfttpd\ConfigurationGenerator;

/**
 * PhpFpmFileTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ConfigurationGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public $path;

    public $generator;

    public $twig;

    public function setUp()
    {
        $this->path = sys_get_temp_dir().'/symfttpd';

        if (!is_dir($this->path)) {
            mkdir($this->path);
        }

        $this->twig = $this->getMock('\Twig_Environment');
        $this->generator = new ConfigurationGenerator($this->twig, $this->getMock('\Symfony\Component\Filesystem\Filesystem'));
    }

    public function tearDown()
    {
        try {
            unlink($this->path);
            rmdir($this->path);
        } catch (\Exception $e) {
            // ...
        }
    }

    /**
     * @testdox should dump the configuration
     */
    public function testDump()
    {
        $this->generator->setPath($this->path);

        $this->generator->dump($this->getMock('\Symfttpd\Gateway\GatewayInterface'));

        $this->assertTrue(file_exists($this->generator->getPath()));
    }

    /**
     * @testdox should generate the configuration
     */
    public function testGenerate()
    {
        $name = 'bar';

        $this->twig->expects($this->once())
            ->method('render')
            ->with($this->equalTo($name.'/'.$name.'.conf.twig'), $this->isType('array'))
            ->will($this->returnValue('foo'));

        $subject = $this->getMock('\Symfttpd\Gateway\GatewayInterface');
        $subject->expects($this->exactly(2))
            ->method('getName')
            ->will($this->returnValue($name));

        $configuration = $this->generator->generate($subject);

        $this->assertFalse(empty($configuration));
        $this->assertEquals('foo', $configuration);
    }
}

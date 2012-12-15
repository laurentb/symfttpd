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

    public $file;

    public $twig;

    public function setUp()
    {
        $this->path = sys_get_temp_dir().'/symfttpd';

        if (!is_dir($this->path)) {
            mkdir($this->path);
        }

        $this->twig = $this->getMock('\Twig_Environment');
        $this->file = new ConfigurationGenerator($this->twig, $this->getMock('\Symfttpd\Filesystem\Filesystem'));
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
        $this->file->setPath($this->path.'/php-fpm.conf');

        $this->file->dump(new \Symfttpd\Tests\Mock\MockGateway());

        $this->assertTrue(file_exists($this->file->getPath()));
    }

    /**
     * @testdox should generate the configuration
     */
    public function testGenerate()
    {
        $template = __DIR__.'/../Fixtures/php-fpm.conf.twig';

        $this->twig->expects($this->once())
            ->method('render')
            ->with($this->equalTo($template), $this->isType('array'))
            ->will($this->returnValue('foo'));

        $this->file->setTemplate($template);
        $configuration = $this->file->generate(new \Symfttpd\Tests\Mock\MockGateway());

        $this->assertFalse(empty($configuration));
        $this->assertEquals('foo', $configuration);
    }
}

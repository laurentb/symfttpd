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

use Symfttpd\SymfttpdFile;
use Symfony\Component\Filesystem\Filesystem;

/**
 * SymfttpdFileTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SymfttpdFileTest extends \PHPUnit_Framework_TestCase
{
    public
        $file,
        $filesystem;

    public function setUp()
    {
        $processor = $this->getMock('\Symfony\Component\Config\Definition\Processor');
        $processor->expects($this->any())
            ->method('processConfiguration')
            ->will($this->returnValue(array()));

        $configuration = $this->getMock('\Symfony\Component\Config\Definition\ConfigurationInterface');

        $this->file = new SymfttpdFile();
        $this->file->setProcessor($processor);
        $this->file->setConfiguration($configuration);

        $this->filesystem = new Filesystem();
        $this->filesystem->touch(sys_get_temp_dir().DIRECTORY_SEPARATOR.'symfttpd.conf.php');
    }

    public function tearDown()
    {
        $this->filesystem->remove(sys_get_temp_dir().DIRECTORY_SEPARATOR.'symfttpd.conf.php');
    }

    public function testAddPath()
    {
        $dir = sys_get_temp_dir();

        $this->file->addPath($dir);

        $this->assertContains($dir, $this->file->getPaths());
    }

    /**
     * @expectedException \Symfttpd\Exception\FileNotFoundException
     */
    public function testSetPathException()
    {
        $dir = '/foo/bar';
        $this->file->addPath($dir);
    }

    public function testRead()
    {
        $configuration = $this->file->read();

        $this->assertInternalType('array', $configuration);
    }
}

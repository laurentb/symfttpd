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

namespace Symfttpd\Tests\Project;

use Symfttpd\Project\Php;

/**
 * PhpTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class PhpTest extends \PHPUnit_Framework_TestCase
{
    protected $project;

    public function setUp()
    {
        $this->project = new Php(new \Symfttpd\Configuration\OptionBag());
    }

    /**
     * @dataProvider directoryGetterTestProvider
     * @param $getter
     * @param $directory
     */
    public function testGetDirectoryGetters($getter, $directory, $expected)
    {
        $getter = 'get'.ucfirst($getter).'Dir';

        $this->project->setRootDir($directory);
        $this->assertEquals($expected, $this->project->$getter());
    }

    public function directoryGetterTestProvider()
    {
        return array(
            array('cache', sys_get_temp_dir(), realpath(sys_get_temp_dir())),
            array('log', sys_get_temp_dir(), realpath(sys_get_temp_dir())),
            array('web', sys_get_temp_dir(), realpath(sys_get_temp_dir())),
        );
    }

    public function testGetIndexFile()
    {
        $this->assertEquals('index.php', $this->project->getIndexFile());
    }

    public function testInitialize()
    {
        $this->project->setRootDir(sys_get_temp_dir());

        $baseDir = $this->project->getRootDir();

        $files = array(
            $baseDir.DIRECTORY_SEPARATOR.'index.php',
            $baseDir.DIRECTORY_SEPARATOR.'robots.txt',
        );

        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $filesystem->remove($baseDir);
        $filesystem->mkdir($baseDir);
        $filesystem->touch($files);

        $this->project->initialize();

        $this->assertContains('index.php', $this->project->readablePhpFiles);
        $this->assertEmpty($this->project->readableDirs);
        $this->assertContains('robots.txt', $this->project->readableFiles);
    }

    public function testGetName()
    {
        $this->assertEquals('php', $this->project->getName());
    }

    public function testGetVersion()
    {
        $this->assertEquals('', $this->project->getVersion());
    }
}


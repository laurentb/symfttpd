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

use Symfttpd\Project\Symfony2;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Symfony2Test class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Symfony2Test extends \PHPUnit_Framework_TestCase
{
    protected $project;

    public function setUp()
    {
        $this->project = new Symfony2(new \Symfttpd\Config());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetPathException()
    {
        $this->project->setRootDir(__DIR__.'/foo/bar');
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
            array('cache', sys_get_temp_dir(), realpath(sys_get_temp_dir()).'/app/cache'),
            array('log', sys_get_temp_dir(), realpath(sys_get_temp_dir()).'/app/logs'),
            array('web', sys_get_temp_dir(), realpath(sys_get_temp_dir()).'/web'),
        );
    }

    public function testGetIndexFile()
    {
        $this->assertEquals('app.php', $this->project->getIndexFile());
    }

    public function testGetRootDir()
    {
        $this->project->setRootDir(sys_get_temp_dir());
        $this->assertEquals(realpath(sys_get_temp_dir()), $this->project->getRootDir());
    }

    public function testGetName()
    {
        $this->assertEquals('symfony', $this->project->getName());
    }

    public function testGetVersion()
    {
        $this->assertEquals('2', $this->project->getVersion());
    }

    public function testGetDefaultExecutableFiles()
    {
        $filesystem = new Filesystem();

        $this->project->setRootDir(sys_get_temp_dir());

        $filesystem->mkdir($this->project->getWebDir());

        $filesystem->touch(array(
            $this->project->getWebDir().'/app.php',
            $this->project->getWebDir().'/app_dev.php',
        ));

        $this->assertInternalType('array', $this->project->getDefaultExecutableFiles());
        $this->assertContains('app.php', $this->project->getDefaultExecutableFiles());
        $this->assertContains('app_dev.php', $this->project->getDefaultExecutableFiles());

        $filesystem->remove(array($this->project->getWebDir()));
    }

    public function testGetDefaultReadableDirs()
    {
        $this->assertInternalType('array', $this->project->getDefaultReadableDirs());
        $this->assertContains('css', $this->project->getDefaultReadableDirs());
        $this->assertContains('js', $this->project->getDefaultReadableDirs());
        $this->assertContains('bundles', $this->project->getDefaultReadableDirs());
    }

    public function testGetDefaultReadableFiles()
    {
        $this->assertInternalType('array', $this->project->getDefaultReadableFiles());
        $this->assertContains('favicon.ico', $this->project->getDefaultReadableFiles());
        $this->assertContains('apple-touch-icon.png', $this->project->getDefaultReadableFiles());
        $this->assertContains('robots.txt', $this->project->getDefaultReadableFiles());
    }
}

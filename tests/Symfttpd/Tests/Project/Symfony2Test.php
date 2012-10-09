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
        $this->assertEquals('index.php', $this->project->getIndexFile());
    }

    public function testGetRootDir()
    {
        $this->project->setRootDir(sys_get_temp_dir());
        $this->assertEquals(realpath(sys_get_temp_dir()), $this->project->getRootDir());
    }

    public function testScan()
    {
        $baseDir = sys_get_temp_dir().'/symfttpd-project-test';

        $projectTree = array(
            $baseDir,
            $baseDir.'/app',
            $baseDir.'/app/cache',
            $baseDir.'/app/config',
            $baseDir.'/src',
            $baseDir.'/app/logs',
            $baseDir.'/web',
            $baseDir.'/web/bundles',
        );

        $files = array(
            $baseDir.'/web/index.php',
            $baseDir.'/web/robots.txt',
        );

        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $filesystem->remove($projectTree);
        $filesystem->mkdir($projectTree);
        $filesystem->touch($files);

        $this->project->setRootDir($baseDir);
        $this->project->scan();

        $this->assertContains('index.php', $this->project->readablePhpFiles);
        $this->assertContains('bundles', $this->project->readableDirs);
        $this->assertContains('robots.txt', $this->project->readableFiles);
    }

    public function testGetName()
    {
        $this->assertEquals('symfony', $this->project->getName());
    }

    public function testGetVersion()
    {
        $this->assertEquals('2', $this->project->getVersion());
    }
}

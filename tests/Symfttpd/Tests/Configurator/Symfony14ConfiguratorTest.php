<?php
/**
 * Symfony14ConfiguratorTest class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 06/11/11
 */

namespace Symfttpd\Tests\Configurator;

use Symfttpd\Tests\Test as BasetestCase;
use Symfttpd\Configurator\Symfony14Configurator;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Filesystem\Filesystem;

class Symfony14ConfiguratorTest extends BaseTestCase
{
    protected $filesystem,
              $project,
              $configurator,
              $configuration;

    public function setUp()
    {
        parent::setUp();

        $this->project = new \Symfttpd\Tests\Fixtures\TestProject();

        $this->filesystem = new Filesystem();
        $this->cleanUp();

        $this->configurator = new Symfony14Configurator();
        $this->configuration = new SymfttpdConfiguration();
        $this->configuration->set('do_plugins', false);
    }

    public function tearDown()
    {
        $this->cleanUp();
    }

    public function testConfigure()
    {
        $this->markTestSkipped();

        $this->configurator->configure($this->project, $this->configuration->all());

        $this->assertTrue(file_exists($this->project->getRootDir().'/cache'), $this->project->getRootDir().'/cache exists');
        $this->assertTrue(file_exists($this->project->getRootDir().'/log'), $this->project->getRootDir().'/log exists');
        $this->assertTrue(is_link($this->project->getRootDir().'/lib/vendor/symfony'), $this->project->getRootDir().'/lib/vendor/symfony exists');
        $this->assertTrue(is_link($this->project->getRootDir().'/web/sf'), $this->project->getRootDir().'/web/sf exists and is a symlink');
    }

    /**
     * @expectedException Symfttpd\Configurator\Exception\ConfiguratorException
     */
    public function testConfigureException()
    {
        $this->markTestSkipped();
        $this->configurator->configure($this->project, $this->configuration->all());
    }

    public function testFindPlugins()
    {
        $plugins = $this->configurator->findPlugins($this->project->getRootDir());

        $this->assertEquals(2, count($plugins));
        $this->assertContains('sfTestPlugin', $plugins);
        $this->assertContains('sfFooBarPlugin', $plugins);
    }

    protected function cleanUp()
    {
        $directories = array(
            $this->project->getRootDir().'/cache',
            $this->project->getRootDir().'/log',
            $this->project->getRootDir().'/plugins',
            $this->project->getRootDir().'/plugins/sfTestPlugin/web',
            $this->project->getRootDir().'/plugins/sfFooBarPlugin/web',
            $this->project->getRootDir().'/config',
            $this->project->getRootDir().'/apps',
            $this->project->getRootDir().'/web',
        );

        $symlinks = array(
            $this->project->getRootDir().'/web/sf',
        );

        $files = array(
            $this->project->getRootDir().'/symfony',
            $this->project->getRootDir().'/web/index.php',
        );

        $this->filesystem->remove($directories + $symlinks);
        $this->filesystem->mkdir($directories);
        $this->filesystem->touch($files);
        $this->filesystem->chmod(reset($files), '755');
    }
}

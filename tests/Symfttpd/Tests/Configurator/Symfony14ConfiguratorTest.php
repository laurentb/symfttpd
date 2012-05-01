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
              $projectPath,
              $configurator,
              $configuration;

    public function setUp()
    {
        parent::setUp();

        $this->projectPath = $this->fixtures.'/symfony-1.4';

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
        $this->configurator->configure($this->projectPath, $this->configuration->all());

        $this->assertTrue(file_exists($this->projectPath.'/cache'), $this->projectPath.'/cache exists');
        $this->assertTrue(file_exists($this->projectPath.'/log'), $this->projectPath.'/log exists');
        $this->assertTrue(is_link($this->projectPath.'/lib/vendor/symfony'), $this->projectPath.'/lib/vendor/symfony exists');
        $this->assertTrue(is_link($this->projectPath.'/web/sf'), $this->projectPath.'/web/sf exists and is a symlink');
    }

    public function testConfigureException()
    {
        $this->setExpectedException('Symfttpd\Configurator\Exception\ConfiguratorException');
        $this->configurator->configure(__DIR__, $this->configuration->all());
    }

    public function testFindPlugins()
    {
        $plugins = $this->configurator->findPlugins($this->projectPath);

        $this->assertEquals(2, count($plugins));
        $this->assertContains('sfTestPlugin', $plugins);
        $this->assertContains('sfFooBarPlugin', $plugins);
    }

    protected function cleanUp()
    {
        $directories = array(
            $this->projectPath.'/cache',
            $this->projectPath.'/log',
            $this->projectPath.'/plugins',
            $this->projectPath.'/plugins/sfTestPlugin/web',
            $this->projectPath.'/plugins/sfFooBarPlugin/web',
            $this->projectPath.'/config',
            $this->projectPath.'/apps',
            $this->projectPath.'/web',
        );

        $symlinks = array(
            $this->projectPath.'/web/sf',
        );

        $files = array(
            $this->projectPath.'/symfony',
            $this->projectPath.'/web/index.php',
        );

        $this->filesystem->remove($directories + $symlinks);
        $this->filesystem->mkdir($directories);
        $this->filesystem->touch($files);
        $this->filesystem->chmod(reset($files), '755');
    }
}

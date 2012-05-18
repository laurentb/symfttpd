<?php
/**
 * SymfttpdConfigurationTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/04/12
 */

namespace Symfttpd\Tests\Configuration;

use Symfttpd\Configuration\SymfttpdConfiguration;

class SymfttpdConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;

    public function setUp()
    {
        $this->configuration = new SymfttpdConfiguration();
    }

    public function testAll()
    {
        $this->assertEquals(16, count($this->configuration->all()));
    }

    public function testGet()
    {
        $this->assertEquals('config/lighttpd.php', $this->configuration->get('genconf'));
        $this->assertEquals('test', $this->configuration->get('unexistentvar', 'test'));
        $this->assertNull($this->configuration->get('unexistentvar'));
    }

    public function testHas()
    {
        $this->assertTrue($this->configuration->has('genconf'));
        $this->assertFalse($this->configuration->has('unexistentvar'));
    }

    public function testSet()
    {
        $this->configuration->set('genconf', 'plop');
        $this->assertEquals('plop', $this->configuration->get('genconf'));
        $this->configuration->set('unexistent', 'plop');
        $this->assertEquals('plop', $this->configuration->get('unexistent'));
    }


    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A project type must be set in the symfttpd.conf.php file.
     */
    public function testGetProjectTypeException()
    {
        $configuration = new SymfttpdConfiguration(array());
        $configuration->clear();

        $configuration->getProjectType();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A project version must be set in the symfttpd.conf.php file.
     */
    public function testGetProjectVersionException()
    {
        $configuration = new SymfttpdConfiguration(array());
        $configuration->clear();
        $configuration->add(array(
            'want' => null,
            'project_type' => 'foo',
            'project_version' => null,
        ));

        $configuration->getProjectVersion();
    }

    public function testGetProjectType()
    {
        $configuration = new SymfttpdConfiguration(array(
            'want' => null,
            'project_type' => 'foo',
        ));

        $this->assertEquals('foo', $configuration->getProjectType());
    }

    public function testGetProjectTypeBC()
    {
        $configuration = new SymfttpdConfiguration(array(
            'want' => '1.4',
            'project_type' => null,
        ));

        $this->assertEquals('symfony', $configuration->getProjectType());
    }

    public function testGetProjectVersion()
    {
        $configuration = new SymfttpdConfiguration(array(
            'want' => null,
            'project_type' => 'foo',
            'project_version' => '1',
        ));

        $this->assertEquals('1', $configuration->getProjectVersion());
    }

    public function testGetPhpProjectVersion()
    {
        $configuration = new SymfttpdConfiguration(array(
            'want' => null,
            'project_type' => 'php',
            'project_version' => '1',
        ));

        $this->assertEquals(null, $configuration->getProjectVersion());
    }

    public function testGetProjectVersionBC()
    {
        $configuration = new SymfttpdConfiguration(array(
            'want' => '1.4',
            'project_type' => null,
            'project_version' => null,
        ));

        $this->assertEquals('1', $configuration->getProjectVersion());
    }


    public function testGetServerTypeBC()
    {
        $configuration = new SymfttpdConfiguration(array(
            'lighttpd_cmd' => 'lighttpd',
            'server_type' => null,
        ));

        $this->assertEquals('lighttpd', $configuration->getServerType());
    }

    public function testGetServerType()
    {
        $configuration = new SymfttpdConfiguration(array(
            'lighttpd_cmd' => null,
            'server_type' => 'foo',
        ));

        $this->assertEquals('foo', $configuration->getServerType());
    }

    public function testGetProjectOptions()
    {
        $configuration = new SymfttpdConfiguration(array(
            'foo' => 'bar',
            'baz' => array('foo', 'bar'),
            'project_readable_dirs'     => array('uploads', 'foo', 'bar'),
            'project_readable_files'    => array('authors.txt'),
            'project_readable_phpfiles' => array('index.php', 'frontend_dev.php'),
            'project_readable_restrict' => false,
        ));

        $this->assertArrayHasKey('project_readable_dirs', $configuration->getProjectOptions());
        $this->assertArrayHasKey('project_readable_files', $configuration->getProjectOptions());
        $this->assertArrayHasKey('project_readable_phpfiles', $configuration->getProjectOptions());
        $this->assertArrayHasKey('project_readable_restrict', $configuration->getProjectOptions());
        $this->assertArrayNotHasKey('foo', $configuration->getProjectOptions());
        $this->assertArrayNotHasKey('baz', $configuration->getProjectOptions());
    }

    public function testGetServerOptions()
    {
        $configuration = new SymfttpdConfiguration(array(
            'foo' => 'bar',
            'baz' => array('foo', 'bar'),
            'server_pidfile'     => 'lighttpd_pid',
            'server_restartfile' => 'lighttpd_restart',
            'server_access_log'  => 'access_log',
            'server_error_log'   => 'error_log',
        ));

        $this->assertArrayHasKey('server_pidfile', $configuration->getServerOptions());
        $this->assertArrayHasKey('server_restartfile', $configuration->getServerOptions());
        $this->assertArrayHasKey('server_access_log', $configuration->getServerOptions());
        $this->assertArrayHasKey('server_error_log', $configuration->getServerOptions());
        $this->assertArrayNotHasKey('foo', $configuration->getServerOptions());
        $this->assertArrayNotHasKey('baz', $configuration->getServerOptions());
    }
}

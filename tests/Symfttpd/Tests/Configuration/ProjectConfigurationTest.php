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

use Symfttpd\Configuration\ProjectConfiguration;

/**
 * ProjectConfigurationTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ProjectConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Configuration\ProjectConfiguration
     */
    public $configuration;

    public function setUp()
    {
        $this->configuration = new ProjectConfiguration();
    }

    public function testGetKeys()
    {
        $keys = $this->configuration->getKeys();

        $this->assertInternalType('array', $keys);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A project type must be set in the symfttpd.conf.php file.
     */
    public function testGetProjectTypeException()
    {
        $this->configuration->clear();
        $this->configuration->getType();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage A project version must be set in the symfttpd.conf.php file.
     */
    public function testGetProjectVersionException()
    {
        $this->configuration->clear();
        $this->configuration->add(array(
                'want' => null,
                'project_type' => 'foo',
                'project_version' => null,
            ));

        $this->configuration->getVersion();
    }

    public function testGetProjectType()
    {
        $this->configuration->clear();
        $this->configuration->add(array(
            'want' => null,
            'project_type' => 'foo',
        ));

        $this->assertEquals('foo', $this->configuration->getType());
    }

    public function testGetProjectTypeBC()
    {
        $this->configuration->clear();
        $this->configuration->add(array(
            'want' => '1.4',
            'project_type' => null,
        ));

        $this->assertEquals('symfony', $this->configuration->getType());
    }

    public function testGetProjectVersion()
    {
        $this->configuration->clear();
        $this->configuration->add(array(
            'want' => null,
            'project_type' => 'foo',
            'project_version' => '1',
        ));

        $this->assertEquals('1', $this->configuration->getVersion());
    }

    public function testGetPhpProjectVersion()
    {
        $this->configuration->clear();
        $this->configuration->add(array(
            'want' => null,
            'project_type' => 'php',
            'project_version' => '1',
        ));

        $this->assertEquals(null, $this->configuration->getVersion());
    }

    public function testGetProjectVersionBC()
    {
        $this->configuration->clear();
        $this->configuration->add(array(
            'want' => '1.4',
            'project_type' => null,
            'project_version' => null,
        ));

        $this->assertEquals('1', $this->configuration->getVersion());
    }
}

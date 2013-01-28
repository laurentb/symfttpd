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

use Symfony\Component\Config\Definition\Processor;
use Symfttpd\Configuration;

/**
 * ConfigurationTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfttpd\Configuration\Configuration
     */
    public $configuration;

    public function setUp()
    {
        $this->configuration = new Configuration();
    }

    /**
     * @dataProvider getConfiguration
     *
     * @param $config
     * @param $expected
     */
    public function testConfiguration($config, $expected)
    {
        $processor = new Processor();

        $processedConfig = $processor->processConfiguration($this->configuration, array('symfttpd' => $config));

        foreach ($expected as $key => $expectation) {
            $this->assertArrayHasKey($key, $processedConfig);
            $this->assertEquals($expectation, $processedConfig[$key]);
        }
    }

    public function getConfiguration()
    {
        return array(
            // PHP project configuration
            array(
                'config'   => array(
                    'project_type'    => 'php',
                    'project_version' => null,
                    'project_readable_dirs'     => array('uploads'),
                    'project_readable_files'    => array('robots.txt'),
                    'project_readable_phpfiles' => array('index.php', 'index_dev.php'),
                    'project_nophp'             => array('uploads'),
                ),
                'expected' => array(
                    'project_type'              => 'php',
                    'project_version'           => null,
                    'project_readable_dirs'     => array(),
                    'project_readable_files'    => array(),
                    'project_readable_phpfiles' => array(),
                    'project_readable_restrict' => true,
                    'project_nophp'             => array(),
                    'project_log_dir'           => 'log',
                    'project_cache_dir'         => 'cache',
                    'project_web_dir'           => 'web',
                    'server_type'               => 'lighttpd',
                    'server_pidfile'            => null,
                    'server_restartfile'        => 'server_restartfile',
                    'server_access_log'         => 'access.log',
                    'server_error_log'          => 'error.log',
                )
            )
        );
    }
}

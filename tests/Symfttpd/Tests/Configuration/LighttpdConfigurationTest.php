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

use Symfttpd\Configuration\LighttpdConfiguration;

/**
 * LighttpdConfigurationTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $configuration;

    public function setUp()
    {
        $this->configuration = new LighttpdConfiguration();
    }

    public function testGenerate()
    {
        $this->configuration->generate($this->getSymfttpdConfiguration(), sys_get_temp_dir());

    }

    public function getSymfttpdConfiguration()
    {
        $configuration = $this->getMock('\Symfttpd\Configuration\SymfttpdConfiguration');
        $configuration->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                'dir'     => 'test',
                'file'    => 'testFile',
                'php'     => 'testPhp',
                'default' => 'testDefault',
                'nophp'   => 'testNophp',
            )
        ));

        return $configuration;
    }
}

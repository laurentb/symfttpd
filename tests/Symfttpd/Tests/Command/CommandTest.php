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

namespace Symfttpd\Tests\Command;

use Symfttpd\Command\Command;
use Symfttpd\Console\Application;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * CommandTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    protected $command;

    public function setUp()
    {
        $this->command = new \Symfttpd\Tests\Fixtures\TestCommand();
    }

    public function testInitialize()
    {
        $tester = new CommandTester($this->command);
        $tester->execute(array(), array());

        $this->assertRegExp('#Symfttpd - version#', $tester->getDisplay());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The command does not know Symfttpd.
     */
    public function testGetSymfttpdException()
    {
        try {
            $this->command->getSymfttpd();
        } catch (\RuntimeException $e) {
            throw $e;
        }

        $this->fail('No exception thrown');
    }
}

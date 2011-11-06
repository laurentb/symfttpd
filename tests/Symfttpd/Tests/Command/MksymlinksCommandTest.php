<?php
/**
 * MksymlinksCommandTest class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 24/10/11
 */

namespace Symfttpd\Tests\Command;

use Symfttpd\Command\MksymlinksCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tester\ApplicationTester;

class MksymlinksCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $command = new MksymlinksCommand();
        $tester  = new CommandTester($command);
        $tester->execute(array('type' => 'symfony', '--ver' => '1.4'), array('interactive' => false));
    }

    /**
     * @expectedException RuntimeException
     * @expecredExceptionMessage Not enough arguments.
     */
    public function testExecuteException()
    {
        $command = new MksymlinksCommand();
        $tester  = new CommandTester($command);
        $tester->execute(array(), array());
    }
}

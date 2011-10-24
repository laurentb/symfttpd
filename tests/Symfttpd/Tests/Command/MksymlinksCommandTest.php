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
        $command = new MksymlinksCommandTest();
        $tester  = new CommandTester($command);
        $tester->execute(array(), array());
    }
}

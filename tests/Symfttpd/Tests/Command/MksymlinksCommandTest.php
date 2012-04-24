<?php
/**
 * MksymlinksCommandTest class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 24/10/11
 */

namespace Symfttpd\Tests\Command;

use Symfttpd\Tests\Test;
use Symfttpd\Command\MksymlinksCommand;
use Symfttpd\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Tester\ApplicationTester;

class MksymlinksCommandTest extends Test
{
    public function setUp()
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->command = new MksymlinksCommand();
        $this->tester  = new CommandTester($this->command);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->fixtures.'/symfony-1.4/web/symfttpd.conf.php');
    }

    public function testExecute()
    {
        $this->tester->execute(array('type' => 'symfony', '--ver' => '1.4', '-p' => $this->fixtures.'/symfony-1.4'), array('interactive' => false));
    }

    /**
     * @expectedException RuntimeException
     * @expecredExceptionMessage Not enough arguments.
     */
    public function testExecuteException()
    {
        $this->tester->execute(array(), array());
    }
}

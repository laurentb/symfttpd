<?php
/**
 * ConfigurationGeneratorCommandTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 28/10/11
 */

namespace Symfttpd\Tests\Command;

use Symfttpd\Tests\Test as BaseTestCase;
use Symfttpd\Command\ConfigurationGeneratorCommand;
use Symfttpd\Util\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigurationGeneratorCommandTest extends BaseTestCase
{
    /**
     * @var Symfttpd\Util\Filesystem $filesystem
     */
    protected $filesystem;

    /**
     * @var Symfony\Component\Console\Tester\CommandTester $command
     */
    protected $command;

    /**
     * @var Symfony\Component\Console\Tester\CommandTester
     */
    protected $tester;

    public function setUp()
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->fixtures.'/symfony-1.4/web/symfttpd.conf.php');

        $this->command = new ConfigurationGeneratorCommand();
        $this->tester  = new CommandTester($this->command);
    }

    public function tearDown()
    {
        $this->filesystem->remove($this->fixtures.'/symfony-1.4/web/symfttpd.conf.php');
    }

    /**
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testExecuteException()
    {
        $this->tester->execute(array(), array('interactive' => false));
    }

    public function testExecute()
    {
        $fixtures = $this->fixtures.'/symfony-1.4/config';
        $this->tester->execute(array('path' => $fixtures), array('interactive' => false));

        $this->assertContains('The configuration file has been well generated.', $this->tester->getDisplay());
        $this->assertTrue(file_exists($fixtures.'/symfttpd.conf.php'));
    }
}

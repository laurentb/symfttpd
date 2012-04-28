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
use Symfttpd\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;

class ConfigurationGeneratorCommandTest extends BaseTestCase
{
    /**
     * @var Symfttpd\Filesystem\Filesystem $filesystem
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
        $this->filesystem->remove($this->fixtures.'/cache/lighttpd/lighttpd.conf');

        $this->command = new ConfigurationGeneratorCommand();
        $this->tester  = new CommandTester($this->command);
    }

    public function tearDown()
    {
        //$this->filesystem->remove($this->fixtures.'/cache/lighttpd/lighttpd.conf');
        $this->filesystem->remove($this->fixtures.'/web');
    }

    public function testExecuteException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->tester->execute(array('--path' => $this->fixtures.'/web'), array('interactive' => false));
    }

    public function testExecute()
    {
        $this->filesystem->mkdir($this->fixtures.'/web');
        $this->tester->execute(array('--path' => $this->fixtures.'/web', '--output-dir' => $this->fixtures), array('interactive' => false));

        $this->assertContains('The configuration file has been well generated.', $this->tester->getDisplay());
        $this->assertTrue(file_exists($this->fixtures.'/cache/lighttpd/lighttpd.conf'));
    }
}

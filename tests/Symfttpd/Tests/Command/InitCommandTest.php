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

/**
 * InitCommandTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class InitCommandTest extends \PHPUnit_Framework_TestCase
{
    public function testExecuteCreateInitConfigurationFile()
    {
        $command = new \Symfttpd\Console\Command\InitCommand();

        $application = new \Symfttpd\Console\Application();
        $application->setContainer(new \Pimple());
        $application->add($command);

        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);
        $tester->execute(
            array('command' => $command->getName()),
            array('interactive' => false)
        );

        $symfttpdFile = getcwd().'/symfttpd.conf.php';

        $this->assertFileExists($symfttpdFile);

        unlink($symfttpdFile);
    }

    public function testInteractWithTheUser()
    {
        $command = new \Symfttpd\Console\Command\InitCommand();

        $finder = $this->getMock('\Symfony\Component\Process\ExecutableFinder');
        $finder->expects($this->any())
            ->method('find')
            ->will($this->returnValue('/usr/bin/foo'));

        $application = new \Symfttpd\Console\Application();
        $application->setContainer(new \Pimple(array('finder' => $finder)));
        $application->add($command);

        // Mock the dialog helper to control answers to questions
        $dialog = $this->getMock('\Symfttpd\Console\Helper\DialogHelper');
        $application->getHelperSet()->set($dialog, 'dialog');

        $input = new \Symfony\Component\Console\Input\ArrayInput(array('command' => $command->getName()));
        $input->setInteractive(true);

        // Create the output to inject it in the value map of the mocked dialog
        $output = new \Symfony\Component\Console\Output\StreamOutput(fopen('php://memory', 'w', false));

        $dialog->expects($this->exactly(3))
            ->method('select')
            ->will($this->returnValueMap(
                array(
                    array($output, "<info>What is the type of your project?</info>", array('php', 'symfony'), null, false, 'Value "%s" is invalid', 1),
                    array($output, "<info>Which server server do you want to use?</info>", array('lighttpd', 'nginx'), null, false, 'Value "%s" is invalid', 1),
                    array($output, "<info>Which gateway do you want to use?</info>", array('fastcgi', 'php-fpm'), null, false, 'Value "%s" is invalid', 1),
                )
            ));

        $dialog->expects($this->exactly(3))
            ->method('ask')
            ->will($this->returnValueMap(
                array(
                    array($output, '<info>Which version, 1 or 2?</info><comment>[2]</comment>', '2', null, 2),
                    array($output, '<info>Set the server executable command</info><comment>[/usr/bin/foo]</comment>', '/usr/bin/foo', null, '/user/bin/foo'),
                    array($output, '<info>Set the gateway used by the server</info><comment>[/usr/bin/foo]</comment>', '/usr/bin/foo', null, '/user/bin/foo'),
                )
            ));

        $command->run($input, $output);
    }
}

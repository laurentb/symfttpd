<?php

namespace Symfttpd\Tests\Guesser;

use Symfony\Component\Finder\Finder;
use Symfttpd\Guesser\ProjectGuesser;

/**
 * ProjectGuesserTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ProjectGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuess()
    {
        $checker = $this->getMock('\Symfttpd\Guesser\Checker\CheckerInterface');
        $checker->expects($this->once())
            ->method('check')
            ->will($this->returnValue(true));
        $checker->expects($this->once())
            ->method('getProject')
            ->will($this->returnValue(array('symfony', 2)));

        $guesser = new ProjectGuesser();
        $guesser->registerChecker($checker);

        list($project, $version) = $guesser->guess();

        $this->assertEquals($project, 'symfony');
        $this->assertEquals($version, 2);
    }

    /**
     * @expectedException \Symfttpd\Guesser\Exception\UnguessableException
     */
    public function testGuessException()
    {
        $guesser = new ProjectGuesser();
        $guesser->guess();
    }

    public function testRegisterChecker()
    {
        $guesser = new ProjectGuesser();

        $this->assertEquals(0, count($guesser->getCheckers()));

        $guesser->registerChecker($this->getMock('\Symfttpd\Guesser\Checker\CheckerInterface'));
        $guesser->registerChecker($this->getMock('\Symfttpd\Guesser\Checker\CheckerInterface'));

        $this->assertEquals(2, count($guesser->getCheckers()));
    }
}

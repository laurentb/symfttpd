<?php

namespace Symfttpd\Tests\Guesser\Checker;

use Symfttpd\Guesser\Checker\Symfony1Checker;

/**
 * Symfony1CheckerTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfony1CheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testChecker()
    {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        $basedir = sys_get_temp_dir().'/symfttpd/symfony';

        $filesystem->mkdir(array(
            $basedir.'/apps',
            $basedir.'/config',
            $basedir.'/data',
            $basedir.'/lib',
            $basedir.'/plugin',
            $basedir.'/web',
        ));

        $filesystem->touch(array(
            $basedir.'/symfony',
        ));

        $checker = new Symfony1Checker($basedir);

        $this->assertTrue($checker->check());

        $filesystem->remove($basedir);
    }

    public function testNotCheck()
    {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        $basedir = sys_get_temp_dir().'/symfttpd/symfony';

        $filesystem->mkdir(array($basedir));

        $checker = new Symfony1Checker($basedir);
        $this->assertFalse($checker->check());
    }
}

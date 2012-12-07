<?php

namespace Symfttpd\Tests\Guesser\Checker;

use Symfttpd\Guesser\Checker\Symfony2Checker;

/**
 * Symfony2CheckerTest description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfony2CheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testChecker()
    {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        $basedir = sys_get_temp_dir().'/symfttpd/symfony2';

        $filesystem->mkdir(array(
            $basedir.'/app',
            $basedir.'/src',
            $basedir.'/vendor',
        ));

        $filesystem->touch(array(
            $basedir.'/app/console',
            $basedir.'/app/AppKernel.php',
        ));

        $checker = new Symfony2Checker($basedir);

        $this->assertTrue($checker->check());

        $filesystem->remove($basedir);
    }

    public function testNotCheck()
    {
        $filesystem = new \Symfony\Component\Filesystem\Filesystem();

        $basedir = sys_get_temp_dir().'/symfttpd/symfony2';

        $filesystem->mkdir(array($basedir));

        $checker = new Symfony2Checker($basedir);
        $this->assertFalse($checker->check());
    }
}

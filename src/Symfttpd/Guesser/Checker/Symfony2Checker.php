<?php

namespace Symfttpd\Guesser\Checker;

use Symfony\Component\Finder\Finder;
use Symfttpd\Guesser\Checker\CheckerInterface;

/**
 * Symfony2Checker description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfony2Checker implements CheckerInterface
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $type = 'symfony';

    /**
     * @var string
     */
    protected $version = '2';

    public function __construct($basedir = null)
    {
        $this->directory = $basedir ?: getcwd();
    }

    /**
     * Check if the project is a Symfony2.
     *
     * @return bool
     */
    public function check()
    {
        return $this->checkDirectories() && $this->checkFiles();
    }

    /**
     * @return array
     */
    public function getProject()
    {
        return array($this->type, $this->version);
    }

    /**
     * Check that directory structures matches
     * the Symfony Standard Edition one.
     *
     * @return bool
     */
    protected function checkDirectories()
    {
        $dirsFinder = Finder::create();
        $dirsFinder->directories()
            ->in($this->directory)
            ->name('app')
            ->name('src')
            ->name('web')
            ->depth('== 0')
        ;

        return $dirsFinder->count() == 3;
    }

    /**
     * Check that the project contains a console
     * and the AppKernel.php in the app dir.
     *
     * @return bool
     */
    protected function checkFiles()
    {
        $filesFinder = Finder::create();
        $filesFinder->files()
            ->in($this->directory.'/app')
            ->name('console')
            ->name('AppKernel.php')
        ;

        return $filesFinder->count() == 2;
    }
}

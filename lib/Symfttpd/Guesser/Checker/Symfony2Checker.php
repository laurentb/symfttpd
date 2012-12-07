<?php

namespace Symfttpd\Guesser\Checker;

use Symfony\Component\Finder\Finder;

/**
 * Symfony2Checker description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfony2Checker
{
    /**
     * @var string
     */
    protected $directory;

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
     * Check that directory structures matchs
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
            ->name('vendor')
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

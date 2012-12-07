<?php

namespace Symfttpd\Guesser\Checker;

/**
 * CheckerInterface
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface CheckerInterface
{
    /**
     * @param null $basedir
     */
    public function __construct($basedir = null);

    /**
     * Check that the project matchs the requirements.
     *
     * @return bool
     */
    public function check();

    /**
     * Return the type and version of the project
     *
     * @return array
     */
    public function getProject();
}

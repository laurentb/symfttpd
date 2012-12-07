<?php

namespace Symfttpd\Guesser;

use Symfttpd\Guesser\Checker\CheckerInterface;
use Symfttpd\Guesser\Exception\UnguessableException;

/**
 * ProjectGuesser description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ProjectGuesser
{
    /**
     * @var array
     */
    protected $checkers = array();

    /**
     * Guess the project type and version.
     *
     * @return mixed
     * @throws Exception\UnguessableException
     */
    public function guess()
    {
        foreach ($this->checkers as $checker) {
            if ($checker->check()) {
                return $checker->getProject();
            }
        }

        throw new UnguessableException('Symfttpd cannot guess the type and version of your project.');
    }

    /**
     * Register a checker.
     *
     * @param Checker\CheckerInterface $checker
     */
    public function registerChecker(CheckerInterface $checker)
    {
        $this->checkers[] = $checker;
    }

    /**
     * @return array
     */
    public function getCheckers()
    {
        return $this->checkers;
    }
}

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

namespace Symfttpd\Tail;

use Symfttpd\Tail\TailInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;

/**
 * MultiTail class
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */

class MultiTail implements TailInterface
{
    /**
     * @var array
     */
    protected $tails  = array();

    /**
     * @var array
     */
    protected $styles = array();

    /**
     * @var \Symfony\Component\Console\Formatter\OutputFormatterInterface
     */
    protected $formatter;

    /**
     * @param OutputFormatterInterface $formatter
     */
    public function __construct(OutputFormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Adds a Tail to watch
     * @param string $name Unique name
     * @param Tail $tail
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function add($name, $tail, $style = null)
    {
        $this->tails[$name] = $tail;
        if (null !== $style) {
            $this->formatter->setStyle($name, $style);
        }
    }

    /**
     * Calls consume() on every Tail and displays lines.
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function consume()
    {
        foreach ($this->tails as $name => $tail) {
            while ($this->display($tail, $name)) ;
        }
    }

    /**
     * Display a line from a Tail, if it is valid.
     * @param Tail $tail
     * @param string $name Unique name of the Tail
     * @return boolean Line validity
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function display($tail, $name)
    {
        $line = $tail->consume();

        if (is_string($line)) {
            $message = $name . ': '.$line;
            if ($this->formatter->hasStyle($name)) {
                echo $this->formatter->getStyle($name)->apply($message);
            } else {
                echo $this->formatter->format($message);
            }

            return true;
        }

        return false;
    }
}


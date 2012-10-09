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

/**
 * Tail class
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
class Tail implements TailInterface
{
    protected $path = null;
    protected $first = true;
    protected $pos = 0;

    /**
     * Follow a file
     * @param string $path
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Read a new line a long as it is possible.
     * Goes straight to the end the first time if the file exists.
     * @return mixed a string (including line return), false (EOF) or null (failure)
     *
     * @author Laurent Bachelier <laurent@bachelier.name>
     */
    public function consume()
    {
        $first = $this->first;
        $this->first = false;
        if (!is_readable($this->path)) {
            return null;
        }

        $fd = fopen($this->path, 'r');
        if ($first) {
            fseek($fd, 0, SEEK_END);
        } else {
            fseek($fd, $this->pos, SEEK_SET);
        }
        $line = fgets($fd);
        $this->pos = ftell($fd);

        if ($line === false) {
            /*
             * Detect file truncation.
             * There seem to be no better way, as fseek will accept
             * to go over EOF and fgets will not handle it differently either.
             */
            $stat = fstat($fd);
            if ($stat['size'] < $this->pos) {
                // rewind
                $this->pos = 0;
            }
        }
        fclose($fd);

        return $line;
    }
}

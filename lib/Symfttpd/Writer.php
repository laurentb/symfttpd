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

namespace Symfttpd;

use Symfttpd\Exception\WriterException;

/**
 * Writer class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Writer
{
    /**
     * @param $content
     * @param $file
     * @param bool $force
     * @return mixed
     * @throws WriterException
     */
    public function write($content, $file, $force = false)
    {
        // Don't rewrite existing configuration if not forced to.
        if (false === $force && file_exists($file)) {
            return;
        }

        if (false === file_put_contents($file, $content)) {
            throw new WriterException(sprintf('Cannot generate the file "%s".', $file));
        }
    }
}

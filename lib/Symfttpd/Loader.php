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

use Symfttpd\Exception\LoaderException;

/**
 * Loader class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Loader
{
    /**
     * @param $file
     * @return string
     * @throws \Symfttpd\Exception\LoaderException
     */
    public function load($file)
    {
        if (false == file_exists($file)) {
            throw new LoaderException(sprintf('The file "%s" does not exist.', $file));
        }

        return file_get_contents($file);
    }
}

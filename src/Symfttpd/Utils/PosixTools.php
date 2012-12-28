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

namespace Symfttpd\Utils;

/**
 * PosixTools class
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
class PosixTools
{
    /**
     * Get a process ID from a file, and kill it, and remove the file either way.
     *
     * @param string $pidfile
     *
     * @return bool
     */
    public static function killPid($pidfile)
    {
        if (file_exists($pidfile)) {
            $pid = intval(trim(file_get_contents($pidfile)));
            unlink($pidfile);
            if ($pid) {
                posix_kill($pid, SIGTERM);

                return true;
            }
        }

        return false;
    }
}

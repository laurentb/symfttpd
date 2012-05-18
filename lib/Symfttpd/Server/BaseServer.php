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

namespace Symfttpd\Server;

use Symfttpd\Server\ServerInterface;
/**
 * BaseServer class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class BaseServer implements ServerInterface
{
    static public $configurationKeys = array(
        'server_pidfile',     // The pidfile stores the PID of the server process.
        'server_restartfile', // The file that tells the spawn command to restart the server.
        'server_access_log',  // The server access log file of the server.
        'server_error_log',   // The server error log file of the server.
    );
}

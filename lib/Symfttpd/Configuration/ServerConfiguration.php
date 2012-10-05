<?php
/**
 * This file is part of the Symfttpd Server
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd\Configuration;

use Symfttpd\OptionBag;
use Symfttpd\Configuration\ConfigurationInterface;

/**
 * ServerConfiguration description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ServerConfiguration extends OptionBag implements ConfigurationInterface
{
    /**
     * @var array Keys of the option set of a Server.
     */
    protected $keys = array(
        'server_pidfile',     // The pidfile stores the PID of the server process.
        'server_restartfile', // The file that tells the spawn command to restart the server.
        'server_access_log',  // The server access log file of the server.
        'server_error_log',   // The server error log file of the server.
    );

    /**
     * Return the available options of a Server configuration.
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * Return the type of the server.
     *
     * @return mixed|null|string
     */
    public function getType()
    {
        // BC with 1.0 version
        if (true == $this->has('lighttpd_cmd')
            && false == $this->has('server_type')) {
            return 'lighttpd';
        }

        return $this->get('server_type', 'lighttpd');
    }
}

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

namespace Symfttpd\Gateway;

use Symfttpd\Gateway\BaseGateway;

/**
 * Fastcgi description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Fastcgi extends BaseGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'fastcgi';
    }

    /**
     * @return string
     */
    public function getSocket()
    {
        return sys_get_temp_dir().'/symfttpd" + PID + ".socket';
    }
}

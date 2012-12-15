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

/**
 * GatewayAwareInterface description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface GatewayAwareInterface
{
    /**
     * @param \Symfttpd\Gateway\GatewayInterface $gateway
     *
     * @return mixed
     */
    public function setGateway(GatewayAwareInterface $gateway);

    /**
     * @return \Symfttpd\Gateway\GatewayInterface
     */
    public function getGateway();
}

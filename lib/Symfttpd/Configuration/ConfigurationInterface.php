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

use Symfttpd\OptionBagInterface;

/**
 * ConfigurationInterface interface
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface ConfigurationInterface extends OptionBagInterface
{
    /**
     * Return the available options of a Server configuration.
     *
     * @return array
     */
    public function getKeys();
}

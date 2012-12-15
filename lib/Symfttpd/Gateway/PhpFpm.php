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
use Symfttpd\ConfigurationFile\ConfigurableInterface;

/**
 * PhpFpm description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class PhpFpm extends BaseGateway implements ConfigurableInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'php-fpm';
    }
}

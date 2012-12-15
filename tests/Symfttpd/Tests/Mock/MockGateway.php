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

namespace Symfttpd\Tests\Mock;

use Symfttpd\Gateway\BaseGateway;
use Symfttpd\ConfigurationFile\ConfigurableInterface;

/**
 * MockGateway description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class MockGateway extends BaseGateway implements ConfigurableInterface
{
    public function getName()
    {
        return 'mock';
    }

}

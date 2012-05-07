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

namespace Symfttpd\Tests\Fixtures;

use Symfttpd\Configuration\SymfttpdConfiguration;

/**
 * TestConfiguration class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class TestConfiguration extends SymfttpdConfiguration
{
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }
}

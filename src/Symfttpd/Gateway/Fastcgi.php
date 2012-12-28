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
use Symfttpd\ConfigurationGenerator;

/**
 * Fastcgi description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Fastcgi extends BaseGateway
{
    const TYPE_FASTCGI = 'fastcgi';
    /**
     * @return string
     */
    public function getType()
    {
        return self::TYPE_FASTCGI;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandLineArguments(ConfigurationGenerator $generator)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function start(ConfigurationGenerator $generator)
    {
        // Do nothing yet see issue https://github.com/benja-M-1/symfttpd/issues/38
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        // Do nothing yet see issue https://github.com/benja-M-1/symfttpd/issues/38
    }
}

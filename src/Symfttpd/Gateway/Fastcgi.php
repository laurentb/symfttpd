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
 * Fastcgi gateway definition.
 *
 * Fastcgi is mainly used with lighttpd. For the moment
 * we don't care about making it working with NGinx.
 *
 * @see issue https://github.com/benja-M-1/symfttpd/issues/38
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
        // Fastcgi is run by Lighttpd we don't need to start a process.
        if (null !== $this->logger) {
            $this->logger->debug("{$this->getType()} started.");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stop()
    {
        if (null !== $this->logger) {
            $this->logger->debug("{$this->getType()} stopped.");
        }
    }
}

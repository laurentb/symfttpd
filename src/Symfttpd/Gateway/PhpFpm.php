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
 * PhpFpm description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class PhpFpm extends BaseGateway
{
    const TYPE_PHPFPM = 'php-fpm';

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_PHPFPM;
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandLineArguments(ConfigurationGenerator $generator)
    {
        return array($this->getExecutable(), '-y', $generator->dump($this, true));
    }
}

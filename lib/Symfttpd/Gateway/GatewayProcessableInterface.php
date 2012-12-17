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

use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Gateway\GatewayInterface;
use Symfttpd\ConfigurationGenerator;

/**
 * GatewayProcessableInterface description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface GatewayProcessableInterface extends GatewayInterface
{
    /**
     * @param \Symfttpd\ConfigurationGenerator                  $generator
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     * @throw \RuntimeException
     */
    public function start(ConfigurationGenerator $generator, OutputInterface $output);

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    public function stop(OutputInterface $output);
}

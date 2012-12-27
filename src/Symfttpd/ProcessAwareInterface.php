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

namespace Symfttpd;

use Symfony\Component\Process\ProcessBuilder;

/**
 * ProcessAwareInterface description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface ProcessAwareInterface
{
    /**
     * @param \Symfony\Component\Process\ProcessBuilder $pb
     */
    public function setProcessBuilder(ProcessBuilder $pb);

    /**
     * @return \Symfony\Component\Process\ProcessBuilder
     */
    public function getProcessBuilder();
}

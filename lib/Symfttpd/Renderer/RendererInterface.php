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

namespace Symfttpd\Renderer;

/**
 * RendererInterface interface.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface RendererInterface
{
    /**
     * Render a template.
     *
     * @abstract
     * @param $skeletonDir
     * @param $template
     * @param array $parameters
     * @return mixed
     */
    public function render($skeletonDir, $template, $parameters = array());

    /**
     * Render a template in a file.
     *
     * @abstract
     * @param $skeletonDir
     * @param $template
     * @param $target
     * @param array $parameters
     * @return mixed
     */
    public function renderFile($skeletonDir, $template, $target, $parameters = array());
}

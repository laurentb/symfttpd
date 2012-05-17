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
 * TwigRenderer class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class TwigRenderer
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Render a template.
     *
     * @param $template
     * @param array $parameters
     * @return string
     */
    public function render($template, $parameters = array())
    {
        return $this->twig->render($template, $parameters);
    }

    /**
     * Add a path in the twig loader.
     *
     * @param $path
     * @return mixed
     */
    public function addPath($path)
    {
        return $this->twig->getLoader()->addPath($path);
    }
}

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

use Symfttpd\Writer;

/**
 * TwigRenderer class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class TwigRenderer
{
    protected $twig;

    public function __construct($skeletonDir)
    {
        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDir), array(
            'debug'            => true,
            'cache'            => false,
            'strict_variables' => true,
            'autoescape'       => false,
        ));

        // Add functions
        $this->twig->addFunction('sys_get_temp_dir', new \Twig_Function_Function('sys_get_temp_dir'));
        $this->twig->addFunction('in_array', new \Twig_Function_Function('in_array'));

        // Add filters
        $this->twig->addFilter('preg_quote', new \Twig_Filter_Function('preg_quote'));
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $parameters = array())
    {
        return $this->twig->render($template, $parameters);
    }
}

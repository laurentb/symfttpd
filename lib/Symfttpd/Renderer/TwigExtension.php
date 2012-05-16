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
 * Class TwigExtension description
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
      return 'symfttpd';
    }

  /**
     * Returns a list of filter to add to Twig.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'preg_quote' => new \Twig_Filter_Function('preg_quote'),
        );
    }

    /**
     * Return a list of function to add to Twig.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'sys_get_temp_dir' => new \Twig_Function_Function('sys_get_temp_dir'),
            'in_array'         => new \Twig_Function_Function('in_array'),
        );
    }
}

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

namespace Symfttpd\Server\Generator;

use Symfttpd\Server\ServerInterface;
use Symfttpd\Filesystem\Filesystem;

/**
 * GeneratorInterface
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
interface GeneratorInterface
{
    /**
     * @param \Twig_Environment               $twig
     * @param \Symfttpd\Filesystem\Filesystem $filesystem
     */
    public function __construct(\Twig_Environment $twig, Filesystem $filesystem);

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return string
     */
    public function generate(ServerInterface $server);

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     * @param bool                             $force
     *
     * @throws \RuntimeException
     */
    public function dump(ServerInterface $server, $force = false);

    /**
     * @param $template
     */
    public function setTemplate($template);

    /**
     * @return string
     */
    public function getTemplate();

    /**
     * @param string $path
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getPath();
}

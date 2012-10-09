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

namespace Symfttpd\Server;

use Symfttpd\Config;
use Symfttpd\Loader;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\Server\ServerInterface;
use Symfttpd\Writer;

/**
 * BaseServer class.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
abstract class BaseServer implements ServerInterface
{
    /**
     * @var ProjectInterface
     */
    protected $project;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Symfttpd\Loader
     */
    protected $loader;

    /**
     * @var \Symfttpd\Writer
     */
    protected $writer;

    /**
     * The server config
     *
     * @var \Symfttpd\Config
     */
    public $config;

    /**
     * @param \Symfttpd\Project\ProjectInterface $project
     * @param \Twig_Environment                  $twig
     * @param \Symfttpd\Loader                   $loader
     * @param \Symfttpd\Writer                   $writer
     * @param \Symfttpd\Config                   $config
     */
    public function __construct(ProjectInterface $project, \Twig_Environment $twig, Loader $loader, Writer $writer, Config $config)
    {
        $this->project  = $project;
        $this->twig     = $twig;
        $this->config   = $config;
        $this->loader   = $loader;
        $this->writer   = $writer;
    }
}

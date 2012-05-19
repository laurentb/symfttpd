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

use Symfttpd\Server\ServerInterface;
use Symfttpd\Project\ProjectInterface;
use Symfttpd\OptionBag;
use Symfttpd\Loader;
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
     * The server options
     *
     * @var OptionBag
     */
    public $options;

    /**
     * Return the keys for the server configuration.
     *
     * @var array
     */
    static public $configurationKeys = array(
        'server_pidfile',     // The pidfile stores the PID of the server process.
        'server_restartfile', // The file that tells the spawn command to restart the server.
        'server_access_log',  // The server access log file of the server.
        'server_error_log',   // The server error log file of the server.
    );

    /**
     * @param \Symfttpd\Project\ProjectInterface $project
     * @param \Twig_Environment                  $twig
     * @param \Symfttpd\Loader                   $loader
     * @param \Symfttpd\Writer                   $writer
     * @param \Symfttpd\OptionBag                $options
     */
    public function __construct(ProjectInterface $project, \Twig_Environment $twig, Loader $loader, Writer $writer, OptionBag $options)
    {
        $this->project  = $project;
        $this->twig     = $twig;
        $this->options  = $options;
        $this->loader   = $loader;
        $this->writer   = $writer;
    }
}

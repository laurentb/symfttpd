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

use Symfttpd\Server\Generator\GeneratorInterface;
use Symfttpd\Server\ServerInterface;

/**
 * LighttpdConfiguration description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class LighttpdGenerator implements GeneratorInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return string
     */
    public function generate(ServerInterface $server)
    {
        return $this->twig->render(
            $this->getTemplate(),
            array(
                'document_root' => $server->getDocumentRoot(),
                'port'          => $server->getPort(),
                'bind'          => $server->getAddress(),
                'error_log'     => $server->getErrorLog(),
                'access_log'    => $server->getAccessLog(),
                'pidfile'       => $server->getPidfile(),
                'php_cgi_cmd'   => $server->getFastcgi(),
                'dirs'          => $server->getAllowedDirs(),
                'files'         => $server->getAllowedFiles(),
                'phps'          => $server->getExecutableFiles(),
                'nophp'         => $server->getDeniedDirs(),
                'default'       => $server->getIndexFile(),
            )
        );
    }

    /**
     * @param \Symfttpd\Server\ServerInterface $server
     * @param bool                             $force
     *
     * @return string
     * @throws \RuntimeException
     */
    public function dump(ServerInterface $server, $force = false)
    {
        // Don't rewrite existing configuration if not forced to.
        if (false === $force && file_exists($this->getPath())) {
            return;
        }

        $configuration = $this->generate($server);

        if (false === file_put_contents($this->getPath(), $configuration)) {
            throw new \RuntimeException(sprintf('Cannot generate the file "%s".', $this->getPath()));
        }

        return $configuration;
    }

    /**
     * @param $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}

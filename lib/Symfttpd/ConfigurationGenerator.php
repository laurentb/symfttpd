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

use Symfttpd\Filesystem\Filesystem;

/**
 * ConfigurationGenerator generates and dumps the configuration
 * generated with twig.
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class ConfigurationGenerator
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var \Symfttpd\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param \Twig_Environment               $twig
     * @param \Symfttpd\Filesystem\Filesystem $filesystem
     */
    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
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

    /**
     * @param \Symfttpd\Server\ServerInterface|\Symfttpd\Gateway\GatewayInterface $subject
     * @param bool                  $force
     *
     * @return string
     * @throws \RuntimeException
     */
    public function dump($subject, $force = false)
    {
        // Don't rewrite existing configuration if not forced to.
        if (false === $force && file_exists($this->getPath())) {
            return;
        }

        $configuration = $this->generate($subject);

        $directory = dirname($this->getPath());

        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        if (false === file_put_contents($this->getPath(), $configuration)) {
            throw new \RuntimeException(sprintf('Cannot generate the file "%s".', $this->getPath()));
        }

        return $configuration;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface|\Symfttpd\Gateway\GatewayInterface $subject
     *
     * @return string
     */
    public function generate($subject)
    {
        return $this->twig->render($this->getTemplate(), array('subject' => $subject));
    }
}

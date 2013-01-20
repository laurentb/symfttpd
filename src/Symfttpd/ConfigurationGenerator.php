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

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

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
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param \Twig_Environment                        $twig
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface                 $logger
     */
    public function __construct(\Twig_Environment $twig, Filesystem $filesystem, LoggerInterface $logger = null)
    {
        $this->twig       = $twig;
        $this->filesystem = $filesystem;
        $this->logger     = $logger;
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
     * @param bool                                                                $force
     *
     * @return string            The generated file
     * @throws \RuntimeException
     */
    public function dump($subject, $force = false)
    {
        $file = $this->getPath().'/'.$subject->getType().'.conf';

        // Don't rewrite existing configuration if not forced to.
        if (false === $force && file_exists($file)) {
            return $file;
        }

        $configuration = $this->generate($subject);

        $directory = $this->getPath();

        if (!$this->filesystem->exists($directory)) {
            $this->filesystem->mkdir($directory);
        }

        if (false === file_put_contents($file, $configuration)) {
            throw new \RuntimeException(sprintf('Cannot generate the file "%s".', $this->getPath()));
        }

        if (null !== $this->logger) {
            $this->logger->debug("Configuration for {$subject->getType()} generated in {$file}.");
        }

        return $file;
    }

    /**
     * @param \Symfttpd\Server\ServerInterface|\Symfttpd\Gateway\GatewayInterface $subject
     *
     * @return string
     */
    public function generate($subject)
    {
        return $this->twig->render($subject->getType().'/'.$subject->getType().'.conf.twig', array('subject' => $subject));
    }
}

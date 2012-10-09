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

use Symfony\Component\Config\Definition\Processor;
use Symfttpd\Exception\FileNotFoundException;

/**
 * SymfttpdFile description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SymfttpdFile
{
    /**
     * @var String
     */
    protected $paths;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var \Symfony\Component\Config\Definition\Processor
     */
    protected $processor;

    /**
     * @var |Symfony\Component\Config\Definition\ConfigurationInterface
     */
    protected $configuration;

    /**
     * Set default paths.
     */
    public function __construct($name = 'symfttpd.conf.php')
    {
        $this->name = $name;

        foreach ($this->getDefaultPaths() as $path) {
            try {
                $this->addPath($path);
            } catch (FileNotFoundException $e) {
                continue;
            }
        }
    }

    /**
     * Return default symfttpd file path.
     *
     * @return array
     */
    public function getDefaultPaths()
    {
        return array(
            __DIR__.'/Resources/templates', // Resource directory
            getenv('HOME').'/symfttpd',     // ~/symfttpd/symfttpd.conf.php
            getenv('HOME').'/.symfttpd',    // ~/.symfttpd/symfttpd.conf.php
            getenv('HOME'),                 // ~/.symfttpd.conf.php
            getcwd().'/config',             // project configuration
            getcwd().'/',
        );
    }

    /**
     * @return String
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param $path
     * @throws Exception\FileNotFoundException
     */
    public function addPath($path)
    {
        if (!file_exists($path.DIRECTORY_SEPARATOR.$this->name)) {
            throw new FileNotFoundException(sprintf('Symfttpd file not found in %s', $path));
        }

        $this->paths[] = $path;
    }

    /**
     * Read files found in paths and return the configuration.
     *
     * @return array
     *
     * @todo use Config component cache system
     *       Cache the processed configuration and check if it defers from
     *       the symfttpd.conf.php file.
     */
    public function read()
    {
        $configuration = array();

        foreach ($this->getPaths() as $path) {
            // Look for the ~/.symfttpd.conf.php
            if (getenv('HOME') === $path) {
                $file = $path.DIRECTORY_SEPARATOR.'.'.$this->name;
            } else {
                $file = $path.DIRECTORY_SEPARATOR.$this->name;
            }

            if (file_exists($file)) {
                require $file;
                if (isset($options)) {
                    $configuration = array_merge($options, $configuration);
                    unset($options);
                }
            }
        }

        return $this->process($configuration);
    }

    /**
     * Process the configuration from
     * the configuration definition.
     *
     * @param array $config
     *
     * @return array
     */
    public function process(array $config)
    {
        return $this->processor->processConfiguration($this->configuration, array('symfttpd' => $config));
    }

    /**
     * @param \Symfony\Component\Config\Definition\ConfigurationInterface $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Processor $processor
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Processor
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}

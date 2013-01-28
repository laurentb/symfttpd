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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Filesystem;
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
    protected $filePaths = array();

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

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
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: new Filesystem();

        foreach ($this->getDefaultFilePaths() as $path) {
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
    public function getDefaultFilePaths()
    {
        return array(
            getenv('HOME').'/symfttpd/symfttpd.conf.php',     // ~/symfttpd/symfttpd.conf.php
            getenv('HOME').'/.symfttpd/symfttpd.conf.php',    // ~/.symfttpd/symfttpd.conf.php
            getenv('HOME').'/.symfttpd.conf.php',             // ~/.symfttpd.conf.php
            getcwd().'/config/symfttpd.conf.php',             // project configuration
            $this->getDefaultFilePath(),
        );
    }

    /**
     * @return array
     */
    public function getFilePaths()
    {
        return $this->filePaths;
    }

    /**
     * @param $path
     * @throws Exception\FileNotFoundException
     */
    public function addPath($path)
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException(sprintf('Symfttpd file not found in %s', $path));
        }

        $this->filePaths[] = $path;
    }

    /**
     * Return the file path from where Symfttpd lives.
     *
     * @return string
     */
    public function getDefaultFilePath()
    {
        return getcwd().'/symfttpd.conf.php';
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

        foreach ($this->getFilePaths() as $file) {
            if (file_exists($file)) {
                require $file;
                if (isset($options)) {
                    $configuration = array_merge($configuration, $options);
                    unset($options);
                }
            }
        }

        return $this->process($configuration);
    }

    /**
     * Write the configuration file in the current directory.
     */
    public function write($config = array())
    {
        $this->filesystem->touch($this->getDefaultFilePath());

        $template = <<<PHP
<?php

\$options = %s;
PHP;

        file_put_contents($this->getDefaultFilePath(), sprintf($template, var_export($config, true)));
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
    public function setConfiguration(ConfigurationInterface $configuration)
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
    public function setProcessor(Processor $processor)
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

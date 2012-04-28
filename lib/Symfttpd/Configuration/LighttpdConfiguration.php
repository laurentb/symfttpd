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

namespace Symfttpd\Configuration;

use Symfttpd\Filesystem\Filesystem;
use Symfttpd\Configuration\SymfttpdConfiguration;
use Symfttpd\Configuration\ConfigurationInterface;
use Symfttpd\Configuration\Exception\ConfigurationException;

/**
 * LighttpdConfiguration class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdConfiguration implements ConfigurationInterface
{
    /**
     * @var string
     */
    protected $filename = 'lighttpd.conf';

    /**
     * @var string
     */
    protected $configuration;

    /**
     * @throws \Symffptd\Configuration\Exception\ConfigurationException
     */
    public function read()
    {
        throw new ConfigurationException('The read method is not implemented yet.');
    }

    /**
     * Write the configuration in the lighttpd cache directory.
     *
     * @param null $baseDir
     * @throws Exception\ConfigurationException
     */
    public function write($baseDir = null)
    {
        if (null == $this->configuration) {
            throw new ConfigurationException('The configuration as not been generated.');
        }

        $dir  = $baseDir.$this->getCacheDir();
        $file = $dir.$this->filename;

        if (false == is_dir($dir)) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($dir);
        }

        // $parameters is used in the template file.
        if (false === file_put_contents($file, $this->configuration)) {
            throw new ConfigurationException("Cannot generate the lighttpd configuration file.");
        }
    }

    /**
     * Generate the lighttpd configuration file.
     *
     * @param SymfttpdConfiguration $configuration
     * @param null $baseDir
     * @throws Exception\ConfigurationException
     */
    public function generate(SymfttpdConfiguration $configuration, $baseDir = null)
    {
        $parameters = $configuration->all();

        ob_start();
        require $this->getTemplate();

        $this->configuration = ob_get_clean();
    }

    /**
     * Return the template path.
     *
     * @return string
     */
    public function getTemplate()
    {
        return __DIR__.sprintf('/../Resources/templates/%s.php', $this->filename);
    }

    /**
     * Return the lighttpd log directory.
     *
     * @return string
     */
    public function getLogDir()
    {
        return '/log/lighttpd/';

    }

    /**
     * Return the lighttpd cache directory.
     *
     * @return string
     */
    public function getCacheDir()
    {
        return '/cache/lighttpd/';
    }

    public function getFilename()
    {
        return $this->filename;
    }
}

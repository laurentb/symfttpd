<?php
/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 27/04/12
 */

namespace Symfttpd;

use Symfttpd\Configuration\SymfttpdConfiguration;

/**
 * Symfttpd class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Symfttpd
{
    const VERSION = '2.0.0-beta';

    /**
     * @var \Symfttpd\Configuration\SymfttpdConfiguration
     */
    protected $coniguration;

    /**
     * @param Configuration\SymfttpdConfiguration $configuration
     */
    public function __construct(SymfttpdConfiguration $configuration = null)
    {
        $this->configuration = $configuration ?: new SymfttpdConfiguration();
    }

    /**
     * Return the Symfttpd configuration.
     *
     * @return Configuration\SymfttpdConfiguration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}

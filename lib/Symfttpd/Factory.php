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

use Symfttpd\Symfttpd;
use Symfttpd\Configuration\SymfttpdConfiguration;

/**
 * Factory class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Factory
{
    /**
     * Return a Symfttpd instance.
     *
     * @static
     * @return Symfttpd
     */
    public static function createSymfttpd()
    {
        $config = new SymfttpdConfiguration();

        $symfttpd = new Symfttpd($config);

        return $symfttpd;
    }

    /**
     * Create a new project.
     *
     * @static
     * @param $type The type of the project (Symfony for instance).
     * @param $version The version of the project (2.0 or 2)
     * @return \Symfttpd\Project\ProjectInterface
     * @throws \InvalidArgumentException
     */
    public static function createProject($type, $version)
    {
        $version = str_replace(array('.', '-', 'O'), '', $version);

        $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type).$version);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" in version "%s" is not supported.', $type, $version));
        }

        $project = new $class();

        return $project;
    }

    /**
     * @static
     * @param $type
     * @return \Symfttpd\Server\ServerInterface
     * @throws \InvalidArgumentException
     */
    public static function createServer($type, \Symfttpd\Project\ProjectInterface $project)
    {
        $class = sprintf('Symfttpd\\Server\\%s', ucfirst($type));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not supported.', $type));
        }

        $server = new $class($project);

        return $server;
    }
}

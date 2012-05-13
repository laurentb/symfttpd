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
use Symfttpd\Configuration\OptionBag;

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
        $symfttpd = new Symfttpd(new SymfttpdConfiguration());

        return $symfttpd;
    }

    /**
     * Create a new project.
     *
     * @static
     * @param $type
     * @param $version
     * @param array $options
     * @param null $path
     * @return \Symfttpd\Project\ProjectInterface
     * @throws \InvalidArgumentException
     */
    public static function createProject($type, $version, $options = array(), $path = null)
    {
        // Guess the project class with the type and the version.
        $class = sprintf('Symfttpd\\Project\\%s', ucfirst($type).str_replace(array('.', '-', 'O'), '', $version));

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('"%s" in version "%s" is not supported.', $type, $version));
        }

        if (null == $path) {
            $path = getcwd();
        }

        return new $class(new OptionBag($options), $path);
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

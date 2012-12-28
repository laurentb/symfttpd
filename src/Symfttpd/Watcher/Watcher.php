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

namespace Symfttpd\Watcher;

use Symfttpd\Log\LoggerInterface;
use Symfttpd\Watcher\Resource\ResourceInterface;
use Symfttpd\Watcher\Resource\TrackedResource;

/**
 * Watcher description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Watcher
{
    /**
     * Array of tracked resources.
     *
     * @var array
     */
    protected $resources = array();

    /**
     * @var \Symfttpd\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param string|Resource $resource The tracked resource, e.g. a file or a directory
     * @param callable        $callback The callback to use when the resource changed
     *
     * @throws \InvalidArgumentException
     */
    public function track($resource, $callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('$callback should be a callable, %s given', gettype($callback)));
        }

        if (!$resource instanceof ResourceInterface) {
            $resource = new TrackedResource($resource);
        }

        $this->resources[$this->generaterTrackerId($resource->getResource())] = array(
            'resource' => $resource,
            'callback' => $callback
        );
    }

    /**
     * @param $resource
     *
     * @return bool
     */
    public function isTracked($resource)
    {
        return array_key_exists($this->generaterTrackerId($resource), $this->resources);
    }

    /**
     * Return an identifier of the tracked resource.
     *
     * @param string|TrackedResource $resource
     *
     * @return string
     */
    protected function generaterTrackerId($resource)
    {
        return md5($resource);
    }

    /**
     * Start watching at resources
     *
     * @param int $interval Interval of time in msecond of watching
     * @param int $timeLimit Duration on the watching time
     */
    public function start($interval = 1000, $timeLimit = null)
    {
        $duration = 0;
        $infinity = false;

        if (null === $timeLimit) {
            $infinity  = true;
        }

        if (null !== $this->logger) {
            $this->logger->debug("Starting watching at resources.");
        }

        while($duration <= $timeLimit || $infinity) {
            usleep($duration);
            $duration += $interval;

            foreach ($this->resources as $resource) {
                if ($resource['resource']->hasChanged()) {
                    if (null !== $this->logger) {
                        $this->logger->debug("{$resource['resource']->getResource()} changed");
                    }

                    call_user_func_array($resource['callback'], array($resource['resource']));
                }
            }
        }
    }

    /**
     * @param \Symfttpd\Log\LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}

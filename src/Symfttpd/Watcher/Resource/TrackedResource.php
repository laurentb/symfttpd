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

namespace Symfttpd\Watcher\Resource;

/**
 * TrackedResource description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class TrackedResource implements ResourceInterface
{
    /**
     * @var string
     */
    protected $resource;

    /**
     * @var \DateTime The unix timestamp when the resource has been tracked
     */
    protected $updatedAd;

    /**
     * @param string $resource The filesystem resource
     */
    public function __construct($resource)
    {
        $this->resource  = new \SplFileInfo($resource);
        $this->updatedAd = new \DateTime();
        $this->updatedAd->setTimestamp($this->resource->getCTime());
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource->getRealPath();
    }

    /**
     * {@inheridoc}
     */
    public function hasChanged()
    {
        if ($this->resource->getCTime() > $this->updatedAd->getTimestamp()) {
            $this->updatedAd->setTimestamp($this->resource->getCTime());

            return true;
        }

        return false;
    }
}

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

namespace Symfttpd\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfttpd\Symfttpd;
use Symfttpd\Configuration\SymfttpdConfiguration;

/**
 * Application class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Application extends BaseApplication
{
    /**
     * @var Symfttpd\Symfttpd
     */
    protected $symfttpd;

    /**
     * Constructor
     * Initialize a Symfttpd object.
     *
     * @param \Symfttpd\Symfttpd $symfttpd
     */
    public function __construct(Symfttpd $symfttpd)
    {
        $this->symfttpd = $symfttpd;

        parent::__construct('Symfttpd', Symfttpd::VERSION);
    }

    /**
     * Return Symfttpd
     *
     * @return \Symfttpd\Symfttpd
     */
    public function getSymfttpd()
    {
        return $this->symfttpd;
    }

    /**
     * @param \Symfttpd\Symfttpd $symfttpd
     */
    public function setSymfttpd(Symfttpd $symfttpd)
    {
        $this->symfttpd = $symfttpd;
    }


    /**
     * {@inheritdoc}
     */
    public function add(Command $command)
    {
        $command->setSymfttpd($this->symfttpd);

        return parent::add($command);
    }
}

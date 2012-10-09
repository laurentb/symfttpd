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
use Symfony\Component\Console\Command\Command;
use Symfttpd\Command\GenconfCommand;
use Symfttpd\Command\SpawnCommand;
use Symfttpd\Factory;
use Symfttpd\Symfttpd;

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
     */
    public function __construct()
    {
        parent::__construct('Symfttpd', Symfttpd::VERSION);
    }

    /**
     * Return Symfttpd
     *
     * @return \Symfttpd\Symfttpd
     */
    public function getSymfttpd()
    {
        if ($this->symfttpd === null) {
            $this->symfttpd = Factory::create();
        }

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

    /**
     * Initializes Symfttpd commands.
     *
     * @return array
     */
    public function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new GenconfCommand();
        $commands[] = new SpawnCommand();

        return $commands;
    }
}

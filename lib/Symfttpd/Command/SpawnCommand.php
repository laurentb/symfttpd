<?php
declare(ticks = 1);

/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd\Command;

use Symfttpd\Symfttpd;
use Symfony\Component\Console\Output\NullOutput;
use Symfttpd\Config;
use Symfttpd\Tail\MultiTail;
use Symfttpd\Tail\Tail;
use Symfttpd\Tail\TailInterface;
use Symfttpd\Command\Command;
use Symfttpd\Server\ServerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * SpawnCommand class
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SpawnCommand extends Command
{
    /**
     * @var string
     */
    protected $server;

    protected function configure()
    {
        $this->setName('spawn');
        $this->setDescription('Launch the webserver.');

        // Spawning options
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to listen', 4042)
            ->addOption('bind', 'b', InputOption::VALUE_OPTIONAL, 'The address to bind', '127.0.0.1')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Bind on all addresses')
            ->addOption('tail', 't', InputOption::VALUE_NONE, 'Print the log in the console')
            ->addOption('kill', 'K', InputOption::VALUE_NONE, 'Kill existing running symfttpd')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $output->getFormatter()->setStyle('important', new OutputFormatterStyle('yellow', null, array('bold')));
    }


    /**
     * Run the Symttpd configured server.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface   $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration = $this->symfttpd->getConfig();
        $configuration->add(array(
            // Lighttpd options
            'port' => $input->getOption('port'),
            'bind' => true == $input->getOption('all') ? false : $input->getOption('bind')
        ));

        $server = $this->getSymfttpd()->getServer();

        // Kill other running server in the current project.
        if (true == $input->getOption('kill')) {
            // Kill existing symfttpd instance if found.
            if (file_exists($server->getRestartFile())) {
                \Symfttpd\Utils\PosixTools::killPid($server->getPidfile(), $output);
                unlink($server->getRestartFile());
            }
        }

        // Print the start spawning message.
        $output->write($this->getMessage($configuration, $server));

        // Flush PHP buffer.
        flush();

        $multitail = null;
        if ($input->getOption('tail')) {
            $tailAccess = new Tail($server->getLogDir() . '/' . $configuration->get('access_log', 'access.log'));
            $tailError  = new Tail($server->getLogDir() . '/' . $configuration->get('error_log', 'error.log'));

            $multitail = new MultiTail(new OutputFormatter(true));
            $multitail->add('access', $tailAccess, new OutputFormatterStyle('blue'));
            $multitail->add('error', $tailError, new OutputFormatterStyle('red', null, array('bold')));
            // We have to do it before the fork to capture the startup messages
            $multitail->consume();
        }

        $this->handleSignals($server, $output);

        return $server->start($output, $multitail) ? 1 : 0;
    }

    /**
     * Return the Symfttpd spawning startup message.
     *
     * @param \Symfttpd\Config                 $configuration
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return string
     */
    protected function getMessage(Config $configuration, ServerInterface $server)
    {
        $server->getProject()->scan();

        if (false == $configuration->get('bind')) {
            $boundAddress = 'all-interfaces';
        } else {
            $boundAddress = $configuration->get('bind');
        }

        $bind = $configuration->get('bind');
        $host = in_array($bind, array(null, false, '0.0.0.0', '::'), true) ? 'localhost' : $bind;

        $apps = array();
        foreach ($server->getProject()->readablePhpFiles as $file) {
            if (preg_match('/.+\.php$/', $file)) {
                $apps[$file] = ' http://' . $host . ':' . $configuration->get('port') . '/<info>' . $file . '</info>';
            }
        }

        // Pretty information. Nothing interesting code-wise.
        $text    = <<<TEXT
%s started on <info>%s</info>, port <info>%s</info>.

Available applications:
%s

<important>Press Ctrl+C to stop serving.</important>

TEXT;
        return sprintf($text, $server->name, $boundAddress, $configuration->get('port'), implode("\n", $apps));
    }

    /**
     * @param \Symfttpd\Server\ServerInterface                  $server
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function handleSignals(ServerInterface $server, OutputInterface $output)
    {
        $handler = function () use ($server, $output) {
            $server->stop(new NullOutput());
            $output->writeln(sprintf(PHP_EOL.'<important>Closing %s, bye!</important>', $server->name));

            exit(0);
        };

        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
    }
}

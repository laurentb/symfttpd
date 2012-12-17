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

use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfttpd\Command\Command;
use Symfttpd\Filesystem\Filesystem;
use Symfttpd\Server\ServerInterface;
use Symfttpd\Tail\MultiTail;
use Symfttpd\Tail\Tail;

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
        parent::initialize($input, $output);

        $output->getFormatter()->setStyle('important', new OutputFormatterStyle('yellow', null, array('bold')));
    }

    /**
     * Run the Symttpd configured server.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $this->getSymfttpd()->getServer();

        $address = true == $input->getOption('all') ? null : $input->getOption('bind');
        $port    = $input->getOption('port');

        $server->bind($address, $port);

        // Kill other running server in the current project.
        if (true == $input->getOption('kill')) {
            // Kill existing server instance if found.
            if (file_exists($server->getPidfile())) {
                \Symfttpd\Utils\PosixTools::killPid($server->getPidfile(), $output);
            }
        }

        // Print the start spawning message.
        $output->write($this->getMessage($server));

        // Flush PHP buffer.
        flush();

        $multitail = null;
        if ($input->getOption('tail')) {
            $tailAccess = new Tail($server->getAccessLog());
            $tailError  = new Tail($server->getErrorLog());

            $multitail = new MultiTail(new OutputFormatter(true));
            $multitail->add('access', $tailAccess, new OutputFormatterStyle('blue'));
            $multitail->add('error', $tailError, new OutputFormatterStyle('red', null, array('bold')));
            // We have to do it before the fork to capture the startup messages
            $multitail->consume();
        }

        $this->handleSignals($server, $output);

        try {
            $generator = $this->symfttpd->getGenerator();

            $paths = array_map(function ($path) {
                $info = pathinfo($path);

                return $info['dirname'];
            }, array($generator->getPath(), $server->getAccessLog(), $server->getErrorLog()));

            array_unique($paths);

            $filesystem = new Filesystem();
            $filesystem->mkdir($paths);

            // Run the gateway if needed.
            if ($server instanceof \Symfttpd\Server\GatewayUnawareInterface
                && ($gateway = $server->getGateway()) instanceof \Symfttpd\Gateway\GatewayProcessableInterface
            ) {
                $server->getGateway()->start($generator, $output);
            }

            return $server->start($generator, $output, $multitail) ? 1 : 0;
        } catch (\Exception $e) {
            $output->writeln('<error>The server cannot start</error>');
            $output->writeln(sprintf('<error>%s</error>', trim($e->getMessage(), " \0\t\r\n")));

            return 0;
        }
    }

    /**
     * Return the Symfttpd spawning startup message.
     *
     * @param \Symfttpd\Server\ServerInterface $server
     *
     * @return string
     */
    public function getMessage(ServerInterface $server)
    {
        if (null == ($address = $server->getAddress())) {
            $address = 'localhost';
        }

        $apps = array();
        foreach ($server->getExecutableFiles() as $file) {
            if (preg_match('/.+\.php$/', $file)) {
                $apps[$file] = ' http://' . $address . ':' . $server->getPort() . '/<info>' . $file . '</info>';
            }
        }

        // Pretty information. Nothing interesting code-wise.
        $text = <<<TEXT
%s started on <info>%s</info>, port <info>%s</info>.

Available applications:
%s

<important>Press Ctrl+C to stop serving.</important>

TEXT;

        return sprintf(
            $text,
            $server->getName(),
            null === $server->getAddress() ? 'all interfaces' : $server->getAddress(),
            $server->getPort(),
            implode("\n", $apps)
        );
    }

    /**
     * @param \Symfttpd\Server\ServerInterface                  $server
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function handleSignals(ServerInterface $server, OutputInterface $output)
    {
        $handler = function () use ($server, $output) {
            // Stop the gateway
            if ($server instanceof \Symfttpd\Server\GatewayUnawareInterface
                && ($gateway = $server->getGateway()) instanceof \Symfttpd\Gateway\GatewayProcessableInterface
            ) {
                $server->getGateway()->stop($output);
            }

            $server->stop(new NullOutput());
            $output->writeln(PHP_EOL.'<important>Stop serving, bye!</important>');

            exit(0);
        };

        pcntl_signal(SIGTERM, $handler);
        pcntl_signal(SIGINT, $handler);
    }
}

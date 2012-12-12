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

namespace Symfttpd\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SelfupdateCommand class
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class SelfupdateCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $server;

    protected function configure()
    {
        $this->setName('self-update');
        $this->setAliases(array('selfupdate', 'sup'));
        $this->setDescription('Update Symfttpd to the latest version.');
    }

    /**
     * Update Symfttpd
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Downloading the latest version.</info>');

        $remoteFilename = "http://benja-m-1.github.com/symfttpd/downloads/symfttpd.phar";
        $localFilename  = $_SERVER['argv'][0];
        $tempFilename   = basename($localFilename, '.phar').'-temp.phar';

        try {
            copy($remoteFilename, $tempFilename);
            chmod($tempFilename, 0777 & ~umask());

            // test the phar validity
            $phar = new \Phar($tempFilename);
            // free the variable to unlock the file
            unset($phar);
            rename($tempFilename, $localFilename);

            $output->writeln('<info>Symfttpd successfully updated to version.</info>');
        } catch (\Exception $e) {
            @unlink($tempFilename);
            if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                throw $e;
            }
            $output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
            $output->writeln('<error>Please re-run the self-update command to try again.</error>');
        }

        return 1;
    }
}

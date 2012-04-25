<?php
/**
 * ScriptHandler class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 25/04/12
 */

namespace Symfttpd\Composer;

use Composer\Script\Event;
use Composer\IO\IOInterface;
use Composer\Factory;
use Composer\Config;
use Composer\Repository\PackageRepository;
use Composer\Repository\RepositoryManager;
use Composer\Repository\InstalledFilesystemRepository;
use Composer\Installer;
use Composer\Installer\InstallationManager;
use Composer\Package\Locker;
use Composer\Package\MemoryPackage;
use Composer\Json\JsonFile;

class ScriptHandler
{
    /**
     * Setup the fixtures for the testing.
     *
     * @static
     * @param \Composer\Script\Event $event
     * @return mixed
     */
    public static function setupFixtures(Event $event)
    {
        // Ask the user if he wants to install fixtures.
        $continue = $event->getIO()->askConfirmation('Do you want to install fixtures for tests? [y/n]', false);

        if ($continue)
        {
            $event->getIO()->write('Setting up fixtures for tests');

            // Inqtall symfony 1.4
            self::installSymfony1($event->getIO());
        }

        return;
    }

    /**
     * Install symfony 1.4 in the Dev directory in fixtures.
     *
     * @static
     * @param \Composer\IO\IOInterface $io
     * @return int
     */
    public static function installSymfony1(IOInterface $io)
    {
        $fixturesDir = __DIR__.'/../../tests/fixtures';
        try {
            $composer = Factory::create($io, array('config' => array('vendor-dir' => $fixturesDir.'/Dev')));
        } catch (\InvalidArgumentException $e) {
            $this->io->write($e->getMessage());
            exit(1);
        }

        $jsonLock = new JsonFile($fixturesDir.'/composer.json');

        $composer->setPackage(new MemoryPackage('symfttpd-fixtures', '1.0', '1.0'));
        $composer->setLocker(new Locker($jsonLock, $composer->getRepositoryManager(), 'hash'));

        $repository = new PackageRepository(array(
            "package" => array(
                "version" => "master",
                "name"    => "symfony/symfony1",
                "source"  => array(
                    "url"       => "https://github.com/symfony/symfony1.git",
                    "type"      => "git",
                    "reference" => "master"
                )
            )
        ));

        $composer->getRepositoryManager()->addRepository($repository);

        $install = Installer::create($io, $composer);

        return $install->run() ? 0 : 1;
    }
}

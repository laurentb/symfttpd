<?php
/**
 * ScriptHandler class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 * @since 25/04/12
 */

namespace Symfttpd\Composer;

use Composer\Composer;
use Composer\Script\Event;
use Composer\IO\IOInterface;
use Composer\Package\MemoryPackage;
use Composer\Repository\PackageRepository;
use Composer\Package\Link;
use Composer\Package\LinkConstraint\VersionConstraint;
use Composer\Factory;
use Composer\Installer;
use Composer\Json\JsonFile;
use Composer\Repository\RepositoryManager;
use Composer\Package\Locker;

class ScriptHandler
{
    /**
     * @var \Composer\Package\MemoryPackage
     */
    protected $package;

    /**
     * @var array
     */
    protected $requires = array();

    /**
     * @var array
     */
    protected $repositories = array();

    /**
     * @var bool
     */
    protected $isUpdate = true;

    /**
     * Setup the fixtures for the tests.
     *
     * @static
     * @param \Composer\Script\Event $event
     */
    public static function setupFixtures(Event $event)
    {

        return;
        $handler = new static();

        $handler->isUpdate = strpos($event->getName(), 'update');

        // Ask the user if he wants to install fixtures.
        $continue = $event->getIO()->askConfirmation('Do you want to install or update fixtures for tests? [y/n] ', false);

        if ($continue)
        {
            $event->getIO()->write('Setting up fixtures for tests');

            // Create the main package for fixtures.
            $handler->createFixturesPackage();

            // Create the requires and repositories
            $handler->configureSymfony1();
            $handler->configureSymfony2();

            $handler->package->setRequires($handler->requires);
            $handler->package->setRepositories($handler->repositories);

            // Install the requirements
            $handler->install($event->getIO());
        }
    }

    /**
     * Create the fixture package.
     *
     * It will handle every packages
     * needed for the tests.
     */
    public function createFixturesPackage()
    {
        $this->package = new MemoryPackage('symfttpd/fixtures', '1.0', '1.0');
    }

    /**
     * Configure symfony 1.4 link and repository.
     */
    public function configureSymfony1()
    {
        $this->requires[] = new Link($this->package->getName(), 'symfony/symfony1', null, 'requires', '1.4.*');
        $this->repositories[] = new PackageRepository(array(
            "type"    => "package",
            "package" => array(
                "version" => "1.4",
                "name"    => "symfony/symfony1",
                "source"  => array(
                    "url"       => "https://github.com/symfony/symfony1.git",
                    "type"      => "git",
                    "reference" => "1.4"
                )
            )
        ));
    }

    /**
     * Configure symfony 2 link and repository.
     */
    public function configureSymfony2()
    {
        $this->requires[] = new Link($this->package->getName(), 'symfony/symfony', null, 'requires', '2.1.*');
    }

    /**
     * Install fixtures.
     *
     * @param \Composer\IO\IOInterface $io
     * @return bool
     */
    public function install(IOInterface $io)
    {
        $installer = Installer::create($io, $this->createComposer($io));
        $installer->setUpdate($this->isUpdate);

        return $installer->run() ? 0 : 1;
    }

    /**
     * Create the composer instance.
     *
     * @param \Composer\IO\IOInterface $io
     * @return \Composer\Composer
     */
    protected function createComposer(IOInterface $io)
    {
        $fixturesDir = __DIR__.'/../../../tests/fixtures';

        $composer = Factory::create($io, array('config' => array('vendor-dir' => $fixturesDir.'/Dev')));

        foreach ($this->repositories as $repository) {
            $composer->getRepositoryManager()->addRepository($repository);
        }

        $jsonLock = $this->getJsonLock($fixturesDir);
        $locker   = $this->getLocker($jsonLock, $composer->getRepositoryManager());

        $composer->setLocker($locker);
        $composer->setPackage($this->package);

        return $composer;
    }

    /**
     * Return the locker.
     *
     * @param \Composer\Json\JsonFile $jsonLock
     * @param \Composer\Repository\RepositoryManager $manager
     * @return \Composer\Package\Locker
     */
    protected function getLocker(JsonFile $jsonLock, RepositoryManager $manager)
    {
        return new Locker($jsonLock, $manager, 'hash');
    }

    /**
     * Return the json lock file.
     *
     * @param $path
     * @return \Composer\Json\JsonFile
     */
    protected function getJsonLock($path)
    {
        return new JsonFile($path.'/composer.lock');
    }
}

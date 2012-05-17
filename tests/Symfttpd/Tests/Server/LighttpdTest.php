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

namespace Symfttpd\Tests\Server;

use Symfttpd\Server\Lighttpd;
use Symfttpd\TwigExtension;

/**
 * LighttpdTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdTest extends \PHPUnit_Framework_TestCase
{
    protected $renderer;
    /**
     * @var Symfttpd\Server\Lighttpd
     */
    protected $server;

    public function setUp()
    {
        $this->renderer = new \Twig_Environment(new \Twig_Loader_Filesystem(realpath(__DIR__ . '/../../../../lib/Symfttpd/Resources/templates')));
        $this->renderer->addExtension(new TwigExtension());

        $this->server = $this->createLighttpd(true);
    }

    public function tearDown()
    {
        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $filesystem->remove($this->getProject()->getRootDir());
    }

    public function testGenerateAndReadRule()
    {
        $this->server->generateRules($this->getMock('\Symfttpd\Configuration\SymfttpdConfiguration'));
        $this->assertEquals($this->getGeneratedRules(), (string)$this->server->readRules());
    }

    public function testGenerateAndReadConfiguration()
    {
        $this->server->generateConfiguration($this->getSymfttpdConfiguration());
        $this->assertEquals($this->getGeneratedConfiguration(), $this->server->readConfiguration());
    }

    public function testGenerateAndWrite()
    {
        $this->server->generate($this->getSymfttpdConfiguration());
        $this->assertEquals($this->getGeneratedConfiguration(true) . PHP_EOL . $this->getGeneratedRules(), $this->server->read());
    }

    public function testGetCommand()
    {
        $finder = $this->getMock('\Symfony\Component\Process\ExecutableFinder');
        $finder->expects($this->once())
            ->method('find')
            ->with('lighttpd')
            ->will($this->returnValue('/usr/sbin/lighttpd'));

        $this->assertEquals('/usr/sbin/lighttpd', $this->server->getCommand($finder));

        $this->server->setCommand('/opt/local/sbin/lighttpd');
        $this->assertEquals('/opt/local/sbin/lighttpd', $this->server->getCommand());
    }

    /**
     * @expectedException Symfttpd\Exception\ExecutableNotFoundException
     * @expectedExceptionMessage lighttpd executable not found.
     */
    public function testGetCommandException()
    {
        $finder = $this->getMock('\Symfony\Component\Process\ExecutableFinder');
        $finder->expects($this->once())
            ->method('find')
            ->with('lighttpd')
            ->will($this->returnValue(null));

        $this->server->getCommand($finder);
    }

    public function testGetConfigFilename()
    {
        $this->assertEquals('lighttpd.conf', $this->server->getConfigFilename());
    }

    public function testGetRulesFilename()
    {
        $this->assertEquals('rules.conf', $this->server->getRulesFilename());
    }

    public function testReadRulesFromFile()
    {
        $this->server->generateRules($this->getMock('\Symfttpd\Configuration\SymfttpdConfiguration'));
        $this->server->writeRules(true);

        $lighttpd = $this->createLighttpd(false);
        $this->assertEquals($this->getGeneratedRules(), $lighttpd->readRules());
    }

    public function testReadConfigFromFile()
    {
        $this->server->generateConfiguration($this->getSymfttpdConfiguration());
        $this->server->writeConfiguration(true);

        $lighttpd = $this->createLighttpd(false);
        $this->assertEquals($this->getGeneratedConfiguration(), $lighttpd->readConfiguration());
    }

    public function testReadFromFile()
    {
        $this->server->generate($this->getSymfttpdConfiguration());
        $this->server->write('all', true);

        $lighttpd = $this->createLighttpd(false);
        $this->assertEquals($this->getGeneratedConfiguration(true), $lighttpd->readConfiguration());
        $this->assertEquals($this->getGeneratedRules(), $lighttpd->readRules());
        $this->assertEquals($this->getGeneratedConfiguration(true) . PHP_EOL . $this->getGeneratedRules(), $lighttpd->read());
    }

    /**
     * @expectedException \Symfttpd\Exception\LoaderException
     */
    public function testReadRulesFromFileException()
    {
        $lighttpd = $this->createLighttpd();
        $lighttpd->rotate(true);
        $lighttpd->readRules();
    }

    /**
     * @expectedException \Symfttpd\Exception\LoaderException
     */
    public function testReadConfigFromFileException()
    {
        $lighttpd = $this->createLighttpd();
        $lighttpd->rotate();
        $lighttpd->readConfiguration();
    }

    /**
     * Create a symfony1 project architecture
     *
     * apps
     * cache
     * config
     * lib
     * web
     *   index.php
     *   frontend_dev.php
     *   backend_dev.php
     *
     */
    public function createSymfonyProject($project)
    {
        $baseDir = $project->getRootDir();

        $projectTree = array(
            $baseDir . DIRECTORY_SEPARATOR . 'apps',
            $baseDir . DIRECTORY_SEPARATOR . 'cache',
            $baseDir . DIRECTORY_SEPARATOR . 'config',
            $baseDir . DIRECTORY_SEPARATOR . 'lib',
            $baseDir . DIRECTORY_SEPARATOR . 'log',
            $baseDir . DIRECTORY_SEPARATOR . 'web',
            $baseDir . DIRECTORY_SEPARATOR . 'web/css',
            $baseDir . DIRECTORY_SEPARATOR . 'web/js',
        );

        $files = array(
            $baseDir . DIRECTORY_SEPARATOR . 'web/index.php',
            $baseDir . DIRECTORY_SEPARATOR . 'web/frontend_dev.php',
            $baseDir . DIRECTORY_SEPARATOR . 'web/backend_dev.php',
            $baseDir . DIRECTORY_SEPARATOR . 'web/robots.txt',
            $baseDir . DIRECTORY_SEPARATOR . 'log/frontend.log',
        );

        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $filesystem->remove($projectTree);
        $filesystem->mkdir($projectTree);
        $filesystem->touch($files);
    }

    /**
     * Return a OptionBag mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getOptions()
    {
        $configuration = new \Symfttpd\OptionBag();
        $configuration->add(array(
            'port' => 4042,
            'bind' => '127.0.0.1'
        ));

        return $configuration;
    }

    /**
     * Return a SymfttpdConfiguration
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getSymfttpdConfiguration()
    {
        $configuration = $this->getMock('\Symfttpd\Configuration\SymfttpdConfiguration');

        $configuration->expects($this->once())
            ->method('get')
            ->will($this->returnValueMap(array(array('php_cgi_cmd', null, '/opt/local/bin/php-cgi'))));

        return $configuration;
    }

    public function getProject($reset = true)
    {
        $project = $this->getMockBuilder('\Symfttpd\Project\BaseProject')
            ->setConstructorArgs(array(new \Symfttpd\OptionBag(), sys_get_temp_dir().'/symfttd-test'))
            ->getMockForAbstractClass();

        $project->expects($this->any())
            ->method('getWebDir')
            ->will($this->returnValue(sys_get_temp_dir().'/symfttd-test/web'));

        $project->expects($this->any())
            ->method('getLogDir')
            ->will($this->returnValue(sys_get_temp_dir().'/symfttd-test/log'));

        $project->expects($this->any())
            ->method('getCacheDir')
            ->will($this->returnValue(sys_get_temp_dir().'/symfttd-test/cache'));

        $project->expects($this->any())
            ->method('getIndexFile')
            ->will($this->returnValue('index.php'));

        if (true == $reset) {
            $this->createSymfonyProject($project);
        }

        return $project;
    }

    public function getGeneratedConfiguration($withRules = false)
    {
        $baseDir = $this->getProject(false)->getRootDir();
        $rules = null;
        if ($withRules) {
            $rules = $baseDir . '/cache/lighttpd/rules.conf';
        }

        return $this->renderer->render(
            'lighttpd.conf.twig',
            array(
                'document_root' => $baseDir.'/web',
                'port'          => 4042,
                'bind'          => "127.0.0.1",
                'error_log'     => $baseDir.'/log/lighttpd/error.log',
                'access_log'    => $baseDir.'/log/lighttpd/access.log',
                'pidfile'       => $baseDir.'/cache/lighttpd/.sf',
                'rules_file'    => $rules,
                'php_cgi_cmd'   => '/opt/local/bin/php-cgi',
            )
        );
    }

    public function getGeneratedRules()
    {
        return $this->renderer->render(
            'rules.conf.twig',
            array(
                'dirs'    => array('css', 'js'),
                'files'   => array('robots.txt'),
                'phps'    => array('backend_dev.php', 'frontend_dev.php', 'index.php'),
                'default' => 'index.php',
                'nophp'   => array(),
            )
        );
    }

    public function createLighttpd($reset = true)
    {
        return new Lighttpd(
            $this->getProject($reset),
            $this->renderer,
            new \Symfttpd\Loader(),
            new \Symfttpd\Writer(),
            $this->getOptions()
        );
    }
}

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

/**
 * LighttpdTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class LighttpdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfttpd\Server\Lighttpd
     */
    protected $server;

    public function setUp()
    {
        $this->server = new Lighttpd($this->getProject(), $this->getOptions());
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

    public function testGetConfigurationTemplate()
    {
        $this->assertStringEndsWith('Resources/templates/lighttpd/lighttpd.conf.php', $this->server->getConfigurationTemplate());
    }

    public function testGetRulesTemplate()
    {
        $this->assertStringEndsWith('Resources/templates/lighttpd/rules.conf.php', $this->server->getRulesTemplate());
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
        $this->server->writeRules();

        $lighttpd = new Lighttpd($this->getProject(false), $this->getOptions());
        $this->assertEquals($this->getGeneratedRules(), $lighttpd->readRules());
    }

    public function testReadConfigFromFile()
    {
        $this->server->generateConfiguration($this->getSymfttpdConfiguration());
        $this->server->writeConfiguration();

        $lighttpd = new Lighttpd($this->getProject(false), $this->getOptions());
        $this->assertEquals($this->getGeneratedConfiguration(), $lighttpd->readConfiguration());
    }

    public function testReadFromFile()
    {
        $this->server->generate($this->getSymfttpdConfiguration());
        $this->server->write();

        $lighttpd = new Lighttpd($this->getProject(false), $this->getOptions());
        $this->assertEquals($this->getGeneratedConfiguration(true), $lighttpd->readConfiguration());
        $this->assertEquals($this->getGeneratedRules(), $lighttpd->readRules());
        $this->assertEquals($this->getGeneratedConfiguration(true) . PHP_EOL . $this->getGeneratedRules(), $lighttpd->read());
    }

    /**
     * @expectedException Symfttpd\Configuration\Exception\ConfigurationException
     * @expectedExceptionMessage The rules configuration has not been generated.
     */
    public function testReadRulesFromFileException()
    {
        $lighttpd = new Lighttpd($this->getProject(), $this->getOptions());
        $lighttpd->rotate(true);
        $lighttpd->readRules();
    }

    /**
     * @expectedException Symfttpd\Configuration\Exception\ConfigurationException
     * @expectedExceptionMessage The lighttpd configuration has not been generated.
     */
    public function testReadConfigFromFileException()
    {
        $lighttpd = new Lighttpd($this->getProject(), $this->getOptions());
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
        $configuration = new \Symfttpd\Configuration\OptionBag();
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
        $project = new \Symfttpd\Tests\Fixtures\TestProject();

        if (true == $reset) {
            $this->createSymfonyProject($project);
        }

        $project->initialize();

        return $project;
    }

    public function getGeneratedConfiguration($withRules = false)
    {
        $conf = <<<CONF
server.modules = (
    "mod_rewrite",
    "mod_access",
    "mod_accesslog",
    "mod_setenv",
    "mod_fastcgi",
)

server.document-root  = "%s/web"
server.port           = 4042
server.bind           = "127.0.0.1"

fastcgi.server = ( ".php" =>
  ( "localhost" =>
    (
      "socket" => "%s/symfttpd-php-" + PID + ".socket",
      "bin-path" => "/opt/local/bin/php-cgi -d error_log=/dev/stderr'",
      "max-procs" => 1,
      "max-load-per-proc" => 1,
      "idle-timeout" => 120,
      "bin-environment" => (
        "PHP_FCGI_CHILDREN" => "3",
        "PHP_FCGI_MAX_REQUESTS" => "100",
        "IN_SYMFTTPD" => "1"
      )
    )
  )
)

setenv.add-response-header = ( "X-Symfttpd" => "1",
    "Expires" => "Sun, 17 Mar 1985 00:42:00 GMT" )

include "%s/lighttpd/mime-types.conf"
server.indexfiles     = ("index.php", "index.html",
                        "index.htm", "default.htm")
server.follow-symlink = "enable"
static-file.exclude-extensions = (".php")

# http://redmine.lighttpd.net/issues/406
server.force-lowercase-filenames = "disable"

server.pid-file       = "%s/cache/lighttpd/.sf"

server.errorlog       = "%s/log/lighttpd/error.log"
accesslog.filename    = "%s/log/lighttpd/access.log"

debug.log-file-not-found = "enable"
debug.log-request-header-on-error = "enable"

%s
CONF;

        $templateDir = realpath(__DIR__ . '/../../../../lib/Symfttpd/Resources/templates');
        $baseDir = $this->getProject(false)->getRootDir();
        if ($withRules) {
            $rules = 'include "' . $baseDir . '/cache/lighttpd/rules.conf"' . PHP_EOL;
        } else {
            $rules = '';
        }

        return sprintf(
            $conf,
            $baseDir,
            sys_get_temp_dir(),
            $templateDir,
            $baseDir,
            $baseDir,
            $baseDir,
            $rules
        );
    }

    public function getGeneratedRules()
    {
        $conf = <<<CONF
url.rewrite-once = (
  "^/css/.+" => "$0",
  "^/js/.+" => "$0",

  "^/robots\.txt$" => "$0",

  "^/backend_dev\.php(/[^\?]*)?(\?.*)?" => "/backend_dev.php$1$2",
  "^/frontend_dev\.php(/[^\?]*)?(\?.*)?" => "/frontend_dev.php$1$2",
  "^/index\.php(/[^\?]*)?(\?.*)?" => "/index.php$1$2",

  "^(/[^\?]*)(\?.*)?" => "/index.php$1$2"
)


CONF;

        return $conf;
    }
}

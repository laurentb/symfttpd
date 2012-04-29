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
    protected $server;

    public function setUp()
    {
        $this->createSymfonyProject();

        $this->server = new Lighttpd(sys_get_temp_dir(), $this->getConfiguration());
    }

    public function testGenerateAndReadRule()
    {
        $this->server->generateRules($this->getMock('\Symfttpd\Configuration\SymfttpdConfiguration'));
        $this->assertEquals($this->getGeneratedRules(), (string) $this->server->readRules());
    }

    public function testGenerateAndReadConfiguration()
    {
        $this->server->generateConfiguration($this->getSymfttpdConfiguration());
        $this->assertEquals($this->getGeneratedConfiguration(), $this->server->readConfiguration());
    }

    public function testGenerateAndWrite()
    {
        $this->server->generate($this->getSymfttpdConfiguration());
        $this->assertEquals($this->getGeneratedConfiguration(true).PHP_EOL.$this->getGeneratedRules(), $this->server->read());
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
    public function createSymfonyProject()
    {
        $baseDir = sys_get_temp_dir();

        $projectTree = array(
            $baseDir.DIRECTORY_SEPARATOR.'apps',
            $baseDir.DIRECTORY_SEPARATOR.'cache',
            $baseDir.DIRECTORY_SEPARATOR.'config',
            $baseDir.DIRECTORY_SEPARATOR.'lib',
            $baseDir.DIRECTORY_SEPARATOR.'log',
            $baseDir.DIRECTORY_SEPARATOR.'web',
            $baseDir.DIRECTORY_SEPARATOR.'web/css',
            $baseDir.DIRECTORY_SEPARATOR.'web/js',
        );

        $files = array(
            $baseDir.DIRECTORY_SEPARATOR.'web/index.php',
            $baseDir.DIRECTORY_SEPARATOR.'web/frontend_dev.php',
            $baseDir.DIRECTORY_SEPARATOR.'web/backend_dev.php',
            $baseDir.DIRECTORY_SEPARATOR.'web/robots.txt',
            $baseDir.DIRECTORY_SEPARATOR.'log/frontend.log',
        );

        $filesystem = new \Symfttpd\Filesystem\Filesystem();
        $filesystem->remove($projectTree);
        $filesystem->mkdir($projectTree);
        $filesystem->touch($files);
    }

    /**
     * Return a ConfigurationBag mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getConfiguration()
    {
        $configuration = $this->getMock('\Symfttpd\Configuration\ConfigurationBag');
        $configuration->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap(array(
                array('document_root', null, sys_get_temp_dir().'/web'),
                array('dirs', null, array('css', 'js')),
                array('files', null, array('robots.txt')),
                array('phps', null, array('index.php', 'frontend_dev.php', 'backend_dev.php')),
                array('default', null, 'index'),
                array('nophp', null, array('log')),
                array('port', null, 4042),
                array('bind', null, '127.0.0.1'),
                // Set does not work with the mock.
                array('pidfile', null, sys_get_temp_dir().'/cache/lighttpd/.sf'),
                array('log_dir', null, sys_get_temp_dir().'/log/lighttpd'),
                array('cache_dir', null, sys_get_temp_dir().'/cache/lighttpd'),
            )
        ));

        $configuration->expects($this->any())
            ->method('has')
            ->will($this->returnValueMap(array(
                array('bind', true),
            )
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
            ->will($this->returnValueMap(array(
                array('php_cgi_cmd', null, '/opt/local/bin/php-cgi')
        )));

        return $configuration;
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

include "%s/mime-types.conf"
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

include "%s"


CONF;

        $templateDir = realpath(__DIR__.'/../../../../lib/Symfttpd/Resources/templates');

        if ($withRules) {
            $rules = sys_get_temp_dir().'/cache/lighttpd/rules.conf';
        } else {
            $rules = '';
        }

        return sprintf(
            $conf,
            sys_get_temp_dir(),
            $templateDir,
            sys_get_temp_dir(),
            sys_get_temp_dir(),
            sys_get_temp_dir(),
            $rules
        );
    }

    public function getGeneratedRules()
    {
        $conf = <<<CONF
server.document-root = "%s/web"

url.rewrite-once = (
  "^/css/.+" => "$0",
  "^/js/.+" => "$0",

  "^/robots\.txt$" => "$0",

  "^/index\.php(/[^\?]*)?(\?.*)?" => "/index.php$1$2",
  "^/frontend_dev\.php(/[^\?]*)?(\?.*)?" => "/frontend_dev.php$1$2",
  "^/backend_dev\.php(/[^\?]*)?(\?.*)?" => "/backend_dev.php$1$2",

  "^(/[^\?]*)(\?.*)?" => "/index.php$1$2"
)


CONF;

        return sprintf($conf, sys_get_temp_dir());
    }
}

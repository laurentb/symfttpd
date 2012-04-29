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

        $this->assertEquals(sprintf($conf, sys_get_temp_dir()), (string) $this->server->readRules());
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
            )
        ));

        return $configuration;
    }
}

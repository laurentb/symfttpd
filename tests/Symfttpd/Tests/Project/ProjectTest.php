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

namespace Symfttpd\Tests\Project;

use Symfttpd\Project\Symfony14;

/**
 * ProjectTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class ProjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getOptions
     * @param $options
     */
    public function testInitialize($options, $values)
    {
        $project = new \Symfttpd\Tests\Fixtures\TestProject(new \Symfttpd\Configuration\OptionBag($options));
        $project->initialize();
        $this->assertEquals($values['dirs'], $project->readableDirs);
        $this->assertEquals($values['files'], $project->readableFiles);
        $this->assertEquals($values['php'], $project->readablePhpFiles);
    }

    public function getOptions()
    {
        return array(
            array(
                array(),
                array(
                    'dirs' => array('uploads'),
                    'files' => array('authors.txt'),
                    'php' => array('class.php', 'index.php', 'phpinfo.php')
                )
            ),
            array(
                array('project_readable_restrict' => true),
                array(
                    'dirs' => array('uploads'),
                    'files' => array('authors.txt'),
                    'php' => array('index.php')
                )
            ),
            array(
                array(
                    'project_readable_restrict' => true,
                    'project_readable_phpfiles' => array('index.php', 'phpinfo.php'),
                ),
                array(
                    'dirs' => array('uploads'),
                    'files' => array('authors.txt'),
                    'php' => array('index.php', 'phpinfo.php'),
                )
            ),
            array(
                array(
                    'project_readable_phpfiles' => array('index.php', 'phpinfo.php'),
                ),
                array(
                    'dirs' => array('uploads'),
                    'files' => array('authors.txt'),
                    'php' => array('class.php', 'index.php', 'phpinfo.php'),
                )
            ),
        );
    }
}


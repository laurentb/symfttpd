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

namespace Symfttpd;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration description
 *
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();
        $root    = $builder->root('symfttpd');

        $this->addSymfttpdConfiguration($root);
        $this->addServerConfiguration($root);
        $this->addProjectConfiguration($root);
        $this->addGatewayConfiguration($root);

        $this->addGlobalValidate($root);

        return $builder;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $node
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function addSymfttpdConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('symfttpd_dir')
                    ->defaultValue(getcwd().'/symfttpd')
                ->end()
            ->end();

        return $node;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $node
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function addServerConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('server_type')
                    ->defaultValue('lighttpd')
                ->end()
                ->scalarNode('server_cmd')
                    ->info('The command to use to run the server.')
                ->end()
                ->scalarNode('server_pidfile')
                    ->info('The pidfile stores the PID of the server process.')
                    ->defaultValue('server_pidfile')
                ->end()
                ->scalarNode('server_restartfile')
                    ->info('The file that tells the spawn command to restart the server.')
                    ->defaultValue('server_restartfile')
                ->end()
                ->scalarNode('server_access_log')
                    ->info('The server access log file of the server.')
                    ->defaultValue('access_log')
                ->end()
                ->scalarNode('server_error_log')
                    ->info('The server error log file of the server.')
                    ->defaultValue('error_log')
                ->end()
            ->end();

        return $node;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $node
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     *
     * @todo Check dirs and files existence from the web directory
     */
    public function addProjectConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('project_type')
                    ->defaultValue('php')
                    ->isRequired()
                ->end()
                ->scalarNode('project_version')
                    ->defaultNull()
                ->end()
                ->arrayNode('project_readable_dirs')
                    ->info('Readable directories by the server in the web dir.')
                    ->validate()
                        ->ifString()
                        ->then(function ($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('project_readable_files')
                    ->info('Readable files by the server in the web dir (robots.txt).')
                    ->validate()
                        ->ifString()
                        ->then(function ($v) { return array($v); })
                    ->end()
                    ->defaultValue(array('authors.txt', 'robots.txt'))
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('project_readable_phpfiles')
                    ->info('Executable php files in the web directory (index.php).')
                    ->validate()
                        ->ifString()
                        ->then(function ($v) { return array($v); })
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('project_readable_restrict')
                    ->info('true if no other php files are readable than configured ones or index file.')
                    ->defaultFalse()
                ->end()
                ->arrayNode('project_nophp')
                    ->info('Deny PHP execution in the specified directories (default being uploads).')
                    ->validate()
                        ->ifString()
                        ->then(function ($v) { return array($v); })
                    ->end()
                    ->defaultValue(array('uploads'))
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('project_log_dir')
                    ->defaultValue('log')
                ->end()
                ->scalarNode('project_cache_dir')
                    ->defaultValue('cache')
                ->end()
                ->scalarNode('project_web_dir')
                    ->defaultValue('web')
                ->end()
            ->end();

        return $node;
    }

    /**
     * @param \Symfony\Component\Config\Definition\Builder\NodeDefinition $node
     */
    public function addGlobalValidate(NodeDefinition $node)
    {
        $node
            ->validate()
            ->always(function ($v) {
                $baseDir = $v['project_web_dir'];

                // Validate readable dirs
                $dirs = $v['project_readable_dirs'];
                foreach ($dirs as $key => $dir) {
                    if (!file_exists($baseDir.DIRECTORY_SEPARATOR.$dir)) {
                        unset($dirs[$key]);
                    }
                }

                sort($dirs);
                $v['project_readable_dirs'] = $dirs;

                // Validate readable files
                $files = $v['project_readable_files'];
                foreach ($files as $key => $file) {
                    if (!file_exists($baseDir.DIRECTORY_SEPARATOR.$file)) {
                        unset($files[$key]);
                    }
                }

                sort($files);
                $v['project_readable_files'] = $files;

                // Validate not executable php files
                $dirs = $v['project_nophp'];
                foreach ($dirs as $key => $dir) {
                    if (!file_exists($dir)) {
                        unset($dirs[$key]);
                    }
                }

                $v['project_nophp'] = $dirs;

                // Validate readable php files.
                $files = $v['project_readable_phpfiles'];
                foreach ($files as $key => $file) {
                    if (!strpos($file, '.php')
                        || !file_exists($baseDir.DIRECTORY_SEPARATOR.$file)
                        || in_array($file, $v['project_readable_files'])
                    ) {
                        unset($files[$key]);
                    }
                }

                sort($files);
                $v['project_readable_phpfiles'] = $files;

                return $v;
            })
            ->end()
        ;
    }

    public function addGatewayConfiguration(NodeDefinition $node)
    {
        $node
            ->children()
                ->scalarNode('gateway_type')
                    ->defaultValue('fastcgi')
                ->end()
                ->scalarNode('gateway_cmd')->end()
                // BC
                ->scalarNode('php_cgi_cmd')->end()
            ->end();

        return $node;
    }
}

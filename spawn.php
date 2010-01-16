#!/usr/bin/env php
<?php
error_reporting(E_ALL|E_STRICT);

require dirname(__FILE__).'/lib/Argument.php';
require dirname(__FILE__).'/lib/Template.php';
require dirname(__FILE__).'/lib/FileTools.php';
require dirname(__FILE__).'/lib/PosixTools.php';
require dirname(__FILE__).'/lib/MultiConfig.php';
require dirname(__FILE__).'/lib/Symfony.php';

$project_path = Symfony::getProjectPath();
$options = MultiConfig::get();

$options['port'] = intval(Argument::get('p', 'port', 4042));
$options['project_path'] = $project_path;
$options['config_dir'] = $project_path.'/cache/lighttpd';
$options['log_dir'] = $project_path.'/log/lighttpd';

FileTools::mkdirs($options['config_dir']);
FileTools::mkdirs($options['log_dir']);

PosixTools::setCustomPath($options['custom_path']);
if (empty($options['lighttpd_cmd']))
{
  $options['lighttpd_cmd'] = PosixTools::which('lighttpd');
}

if (empty($options['php_cgi_cmd']))
{
  $options['php_cgi_cmd'] = PosixTools::which('php-cgi');
}

if (empty($options['php_cmd']))
{
  $options['php_cmd'] = PosixTools::which('php');
}

$config_file = $options['config_dir'].'/lighttpd.conf';
file_put_contents(
  $config_file,
  Template::get($options['config_template'], $options)
);
echo "lighttpd started on http://localhost:${options['port']}/\n";
passthru($options['lighttpd_cmd'].' -D -f '.$config_file);


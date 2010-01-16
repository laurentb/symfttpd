#!/usr/bin/env php
<?php
error_reporting(E_ALL|E_STRICT);

require dirname(__FILE__).'/lib/Argument.php';
require dirname(__FILE__).'/lib/Template.php';
require dirname(__FILE__).'/lib/FileTools.php';
require dirname(__FILE__).'/lib/PosixTools.php';
require dirname(__FILE__).'/lib/MultiConfig.php';
require dirname(__FILE__).'/lib/Symfony.php';

$options = MultiConfig::get();
$arguments = array(
  'port' => intval(Argument::get('p', 'port', 4042)),
);

$project_path = Symfony::getProjectPath();

FileTools::mkdirs($project_path.'/cache/lighttpd');
FileTools::mkdirs($project_path.'/logs/lighttpd');

PosixTools::setCustomPath($options['custom_path']);
if (empty($options['lighttpd_cmd']))
{
  $options['lighttpd_cmd'] = PosixTools::which('lighttpd');
}

if (empty($options['php-cgi_cmd']))
{
  $options['php-cgi_cmd'] = PosixTools::which('php-cgi');
}

//$template = get_template('lighttpd.conf', $options);

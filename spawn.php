#!/usr/bin/env php
<?php
require dirname(__FILE__).'/lib/getopt.php';
require dirname(__FILE__).'/lib/template.php';
require dirname(__FILE__).'/lib/sfTools.class.php';

$options = array(
  'port' => intval(get_opt('p', 'port', 4042)),
  'project_path' => realpath(get_opt('P', 'path', getcwd())),
);

if (!is_file($options['project_path'].'/symfony'))
{
  throw new Exception('Not in a symfony project');
}

sfTools::mkdirs($options['project_path'].'/cache/lighttpd');
sfTools::mkdirs($options['project_path'].'/logs/lighttpd');

//$template = get_template('lighttpd.conf', $options);

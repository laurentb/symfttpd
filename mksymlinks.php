#!/usr/bin/env php
<?php
/**
 * Create the necessary symlinks to setup a symfony project
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */

error_reporting(E_ALL|E_STRICT);

/**
 * Get config options from multiple files
 * @return array
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
function get_options()
{
  $options = array();
  $cfgname = 'symfttpd.conf.php';

  $configs = array(
      dirname(__FILE__).'/'.$cfgname, // defaults
      getenv('HOME').'/.'.$cfgname, // user configuration
      getcwd().'/config/'.$cfgname, // project configuration
  );

  foreach ($configs as $config)
  {
    if (file_exists($config))
    {
      require $config;
    }
  }

  return $options;
}

/**
 * @param string $target The destination of the symlink
 * @param string $link The path of the symlink to create
 * @return boolean Success
 */
function replace_symlink($target, $link)
{
  // Erase only if it is already a symlink
  if (is_link($link))
  {
    unlink($link);
  }
  log_message(' '.$link.' => '.$target);

  return symlink($target, $link);
}

function log_message($message)
{
  echo $message."\n";
}


$options = get_options();

$project_path = getcwd();
if (!is_file($project_path.'/symfony'))
{
  throw new Exception('Not in a symfony project');
}

log_message("Using symfony version ".$options['want']);
$symlinks = array();
if ($options['genconf'])
{
  $symlinks[$options['genconf']] = $options['path'].'/genconf.php';
}

$sf_path = $options['sf_path'][$options['want']];
foreach (array(
    'symfony_symlink' => '',
    'lib_symlink' => 'lib',
    'data_symlink' => 'data',
    'web_symlink' => 'data/web/sf',
  )
  as $option => $relpath)
{
  $link = $options[$option];
  if ($link)
  {
    $target = realpath($sf_path.'/'.$relpath);
    if (!is_dir($target))
    {
      throw new Exception($target.' is not a directory');
    }
    $symlinks[$link] = $target;
  }
}

log_message('Creating symbolic links...');
foreach ($symlinks as $link => $target)
{
  replace_symlink($target, $link);
}
if ($options['do_plugins'])
{
  if (version_compare($options['want'], '1.2') >= 0)
  {
    log_message('Creating symbolic links for plugins...');
    system('/usr/bin/env php symfony plugin:publish-assets');
  }
  else
  {
    log_message('WARNING: Creating symbolic links for plugins is'
       . 'not yet supported for symfony < 1.2');
  }
}

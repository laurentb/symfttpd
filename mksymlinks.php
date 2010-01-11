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

var_dump(get_options());

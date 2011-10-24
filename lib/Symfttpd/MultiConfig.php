<?php

namespace Symfttpd;

class MultiConfig
{
  /**
   * Get config options from multiple files
   * @return array
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function get($cfgname = 'symfttpd.conf.php')
  {
    $options = array();

    $configs = array(
      __DIR__.'/../../'.$cfgname, // defaults
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
}

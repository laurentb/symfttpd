<?php
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
    if (file_exists(getcwd().'/symfttpd') && is_dir(getcwd().'/symfttpd'))
    {
      $options['has_symfttpd_dir'] = true;
    }

    $configs = array(
      dirname(__FILE__).'/../'.$cfgname, // defaults
      getenv('HOME').'/.'.$cfgname, // user configuration
      getcwd().'/config/'.$cfgname, // project configuration
      getcwd().'/symfttpd/'.$cfgname, // project configuration, new style
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

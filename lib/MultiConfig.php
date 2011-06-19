<?php
class MultiConfig
{
  /**
   * Get config options from multiple files
   * @param $project_path Path of the current project
   * @param $cfgname Base name of the configuration file.
   * @return array
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function get($project_path, $cfgname = 'symfttpd.conf.php')
  {
    $options = array();
    if (file_exists($project_path.'/symfttpd') && is_dir($project_path.'/symfttpd'))
    {
      $options['has_symfttpd_dir'] = true;
    }

    $configs = array(
      dirname(__FILE__).'/../'.$cfgname, // defaults
      getenv('HOME').'/.'.$cfgname, // user configuration
      $project_path.'/config/'.$cfgname, // project configuration
      $project_path.'/symfttpd/'.$cfgname, // project configuration, new style
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

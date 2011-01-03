<?php
class Template
{
  /**
   * Process a template (simple PHP file)
   * @param string $_file
   * @param array $_data
   * @return string
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function get($_file, array $_data = array())
  {
    extract($_data);
    unset($_data);
    ob_start();

    require $_file;

    return ob_get_clean();
  }

  /**
   * Write the main lighttpd config file
   * @param array $options
   * @return boolean|integer Failure or number of bytes written
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function writeConfig($options)
  {
    $config_file = $options['config_dir'].'/lighttpd.conf';

    return file_put_contents(
      $config_file,
      Template::get($options['config_template'], $options)
    );
  }

}

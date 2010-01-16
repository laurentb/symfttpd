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
}

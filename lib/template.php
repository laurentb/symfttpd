<?php
/**
 * Process a template (simple PHP file)
 * @param string $file
 * @param array $data
 * @return string
 *
 * @author Laurent Bachelier <laurent@bachelier.name>
 */
function get_template($_file, array $_data = array())
{
  extract($_data);
  unset($_data);
  ob_start();

  require $_file;

  return ob_get_clean();
}

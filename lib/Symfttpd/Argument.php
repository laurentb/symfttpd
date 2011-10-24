<?php

namespace Symfttpd;


if (version_compare(PHP_VERSION, '5.3.0') < 0)
{
  require dirname(__FILE__) . '/getopt.php';
}

class Argument
{
  /**
   * Get command line options in a sane way.
   *
   * Examples:
   *    getopt("p", "port", 42)
   *    getopt("n", "nofork", false)
   *    getopt("P", "path", null, true)
   * @param string $short Short option (one letter)
   * @param string $long Long option (many letters)
   * @param mixed $default "42". If strictly equal to false, the option \
   *   is considered a boolean (option present returning true, false otherwise)
   * @param boolean $required Required option (ignored if $default is false)
   * @return string|boolean
   */
  static public function get($short, $long, $default, $required = false)
  {
    $addon = $default === false ? '' : ':';

    $options = (version_compare(PHP_VERSION, '5.3.0') < 0)
      ? _getopt($short.$addon, array($long.$addon))
      : getopt($short.$addon, array($long.$addon));

    $value = $default;
    if (isset($options[$short]))
    {
      $value = $default === false ? true : $options[$short];
    }
    if (isset($options[$long]))
    {
      $value = $default === false ? true : $options[$long];
    }

    if ($required && is_null($value))
    {
      throw new Exception('Missing required "'.$long.'" parameter');
    }

    return $value;
  }
}

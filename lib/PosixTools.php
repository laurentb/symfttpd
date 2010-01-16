<?php

class ExecutableNotFoundException extends Exception
{
  protected $executable;

  public function __construct(
    $executable, $code = 0, Exception $previous = null)
  {
    $this->executable = $executable;
    $this->message = $executable.' not found in path';
  }
}

class PosixTools
{
  static protected $custom_path = array();

  /**
   * @param string $command e.g. "lighttpd"
   * @return string
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function which($command)
  {
    foreach (self::getPaths() as $dir)
    {
      $path = $dir.'/'.$command;
      if (is_executable($path))
      {

        return realpath($path);
      }

    }

    throw new ExecutableNotFoundException($command);
  }

  /**
   * Get a list of directories where executables are supposed to be (taken
   * from the PATH environment variable).
   * Some directories can be added to the standard PATH variable, since
   * in some cases the "sbin" directories are not exposed to normal users.
   * @return array
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static protected function getPaths()
  {

    return array_merge(
      explode(':', getenv('PATH')),
      self::$custom_path
    );
  }

  /**
   * @param $custom_path array e.g. array("/usr/local/sbin")
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function setCustomPath(array $custom_path)
  {
    self::$custom_path = $custom_path;
  }

}


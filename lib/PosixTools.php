<?php

class ExecutableNotFoundError extends Exception
{
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
      $path = realpath($dir.'/'.$command);
      if (!is_dir($path) && is_executable($path))
      {

        return $path;
      }
    }

    throw new ExecutableNotFoundError($command);
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
  static public function getPaths()
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

  /**
   * Get a process ID from a file, and kill it, and remove the file either way.
   * @param $pidfile Path to PID file
   * @return boolean Success
   *
   * @author Laurent Bachelier <laurent@bachelier.name>
   */
  static public function killPid($pidfile)
  {
    if (file_exists($pidfile))
    {
      $pid = intval(trim(file_get_contents($pidfile)));
      unlink($pidfile);
      if ($pid)
      {
        posix_kill($pid, SIGTERM);
        log_message('Process '.$pid.' killed');

        return true;
      }
    }
    log_message('No running process found', true);

    return false;
  }
}


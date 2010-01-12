<?php
/**
 * Various functions, usually trimmed down, from symfony
 */

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfTools
{
  /**
   * Creates a symbolic link or copy a directory.
   *
   * @param string $originDir      The origin directory path
   * @param string $targetDir      The symbolic link name
   */
  static public function symlink($originDir, $targetDir)
  {
    $ok = false;
    if (is_link($targetDir))
    {
      if (readlink($targetDir) != $originDir)
      {
        unlink($targetDir);
      }
      else
      {
        $ok = true;
      }
    }

    if (!$ok)
    {
      symlink($originDir, $targetDir);
    }
  }

  /**
   * Creates a symbolic link using a relative path if possible.
   *
   * @param string $originDir      The origin directory path
   * @param string $targetDir      The symbolic link name
   */
  static public function relativeSymlink($originDir, $targetDir)
  {
    $originDir = self::calculateRelativeDir($targetDir, $originDir);
    self::symlink($originDir, $targetDir);
  }

  /**
   * Calculates the relative path from one to another directory.
   *
   * If the paths share no common path the absolute target dir is returned.
   *
   * @param string $from The directory from which to calculate the relative path
   * @param string $to   The target directory
   *
   * @return string
   */ 
  protected function calculateRelativeDir($from, $to)
  {
    $from = self::canonicalizePath($from);
    $to = self::canonicalizePath($to);

    $commonLength = 0;
    $minPathLength = min(strlen($from), strlen($to));

    // count how many chars the strings have in common
    for ($i = 0; $i < $minPathLength; $i++)
    {
      if ($from[$i] != $to[$i])
      {
        break;
      }

      if (DIRECTORY_SEPARATOR == $from[$i])
      {
        $commonLength = $i + 1;
      }
    }

    if ($commonLength)
    {
      $levelUp = substr_count($from, DIRECTORY_SEPARATOR, $commonLength);

      // up that many level
      $relativeDir = str_repeat('..'.DIRECTORY_SEPARATOR, $levelUp);

      // down the remaining $to path
      $relativeDir .= substr($to, $commonLength);

      return $relativeDir;
    }

    return $to;
  }

  /**
   * @param string A filesystem path
   *
   * @return string
   */
  protected function canonicalizePath($path)
  {
    if (empty($path))
    {
      return '';
    }

    $out = array();
    foreach (explode(DIRECTORY_SEPARATOR, $path) as $i => $fold)
    {
      if ('' == $fold || '.' == $fold)
      {
        continue;
      }

      if ('..' == $fold && $i > 0 && '..' != end($out))
      {
        array_pop($out);
      }
      else
      {
        $out[] = $fold;
      }
    }

    $result  = DIRECTORY_SEPARATOR == $path[0] ? DIRECTORY_SEPARATOR : '';
    $result .= implode(DIRECTORY_SEPARATOR, $out);
    $result .= DIRECTORY_SEPARATOR == $path[strlen($path) - 1] ? DIRECTORY_SEPARATOR : '';

    return $result;
  }
}

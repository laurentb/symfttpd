<?php
/**
 * Various functions, usually trimmed down, from symfony
 * and with less insane defaults
 */

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfttpd;

class FileTools
{
  /**
   * Creates a symbolic link or copy a directory.
   *
   * @param string $originDir      The origin directory path
   * @param string $targetDir      The symbolic link name
   */
  static public function symlink($originDir, $targetDir)
  {
    if (is_link($targetDir))
    {
      if (readlink($targetDir) == $originDir)
      {
        // Nothing to do here!

        return true;
      }
      else
      {
        unlink($targetDir);
      }
    }

    return symlink($originDir, $targetDir);
  }
}

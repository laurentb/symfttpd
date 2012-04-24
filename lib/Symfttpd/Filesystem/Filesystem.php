<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfttpd\Filesystem;

/**
 * Provides basic utility to manipulate the file system.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Filesystem extends Symfony\Component\Filesystem\Filesystem
{

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
    public function calculateRelativeDir($from, $to)
    {
        $from = $this->canonicalizePath($from);
        $to = $this->canonicalizePath($to);

        $commonLength = 0;
        $minPathLength = min(strlen($from), strlen($to));

        // count how many chars the strings have in common
        for ($i = 0; $i < $minPathLength; $i++)
        {
            if ($from[$i] != $to[$i]) {
                break;
            }

            if (DIRECTORY_SEPARATOR == $from[$i]) {
                $commonLength = $i + 1;
            }
        }

        if ($commonLength) {
            $levelUp = substr_count($from, DIRECTORY_SEPARATOR, $commonLength);

            // up that many level
            $relativeDir = str_repeat('..' . DIRECTORY_SEPARATOR, $levelUp);

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
        if (empty($path)) {
            return '';
        }

        $out = array();
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $i => $fold) {
            if ('' == $fold || '.' == $fold) {
                continue;
            }

            if ('..' == $fold && $i > 0 && '..' != end($out)) {
                array_pop($out);
            } else {
                $out[] = $fold;
            }
        }

        $result = DIRECTORY_SEPARATOR == $path[0] ? DIRECTORY_SEPARATOR : '';
        $result .= implode(DIRECTORY_SEPARATOR, $out);
        $result .= DIRECTORY_SEPARATOR == $path[strlen($path) - 1] ? DIRECTORY_SEPARATOR : '';

        return $result;
    }
}

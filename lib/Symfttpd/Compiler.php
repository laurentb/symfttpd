<?php
/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Compiler class
 *
 * Partly taken from Composer and Silex compiler class.
 * @see https://github.com/composer/composer/blob/master/src/Composer/Compiler.php
 * @see https://github.com/fabpot/Silex/blob/master/src/Silex/Compiler.php
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class Compiler
{
    /**
     * Compile symfttpd.
     *
     * @param  string            $pharFile
     * @throws \RuntimeException
     */
    public function compile($pharFile = 'symfttpd.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $process = new Process('git describe --tags HEAD');
        if ($process->run() == 0) {
            $this->version = trim($process->getOutput());
        }
        $phar = new \Phar($pharFile, 0, 'symfttpd.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        // Add Symfttpd core files.
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->name('*.twig')
            ->in(__DIR__.'/..')
            ->notName('Compiler.php')
            ->exclude('Tests');

        foreach ($finder as $file) {
            $this->addFile($phar, $file, false);
        }

        // Add vendors
        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->in(__DIR__.'/../../vendor/symfony')
            ->in(__DIR__.'/../../vendor/twig')
            ->exclude(__DIR__.'/../../vendor/twig/doc')
            ->exclude(__DIR__.'/../../vendor/twig/test')
        ;

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        // Add Composer generated files.
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/include_paths.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/autoload_real.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__.'/../../vendor/composer/ClassLoader.php'));
        $this->addSymfttpdBin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();
    }

    private function addFile(\Phar $phar, $file, $strip = true)
    {
        $path = str_replace(dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR, '', $file->getRealPath());

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        }

        $content = str_replace('@package_version@', $this->version, $content);

        $phar->addFromString($path, $content);
    }

    private function addSymfttpdBin($phar)
    {
        $content = file_get_contents(__DIR__.'/../../bin/symfttpd');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/symfttpd', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

Phar::mapPhar('symfttpd.phar');

require 'phar://symfttpd.phar/bin/symfttpd';

__HALT_COMPILER();
EOF;
    }
}

<?php
/**
 * @author Benjamin Grandfond <benjamin.grandfond@gmail.com>
 * @since 24/10/2011
 */

require_once __DIR__.'/lib/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$loader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'  => __DIR__.'/lib/vendor',
    'Monolog'  => __DIR__.'/lib/vendor/Monolog/src',
    'Symfttpd' => __DIR__.'/lib/'
));

$loader->register();

<?php

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->registerNamespace('Doctrine', $_SERVER['DOCTRINE']);
$loader->register();

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'FOS\\RestBundle\\')) {
        $path = implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path(__DIR__.'/../'.$path)) {
            return false;
        }
        require_once __DIR__.'/../'.$path;
        return true;
    }
});

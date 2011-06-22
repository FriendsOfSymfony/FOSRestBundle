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
        $path = __DIR__.'/../'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }
        require_once $path;
        return true;
    }
    if (0 === strpos($class, 'Sensio\\Bundle\\FrameworkExtraBundle\\')) {
        $path = __DIR__.'/../../../Sensio/'.implode('/', array_slice(explode('\\', $class), 1)).'.php';
        if (!stream_resolve_include_path($path)) {
            return false;
        }
        require_once $path;
        return true;
    }
});

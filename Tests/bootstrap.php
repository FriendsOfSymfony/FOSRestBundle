<?php

require_once $_SERVER['SYMFONY'].'/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespace('Symfony', $_SERVER['SYMFONY']);
$loader->registerNamespace('Doctrine', $_SERVER['DOCTRINE']);
$loader->registerNamespace('Sensio\\Bundle\\FrameworkExtraBundle\\', __DIR__.'/../../..');
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
});

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader(function($class) {
    if (strpos($class, 'FOS\RestBundle\Controller\Annotations\\') === 0) {
        $path = __DIR__.'/../'.str_replace('\\', '/', substr($class, strlen('FOS\RestBundle\\')))   .'.php';
        require_once $path;
    }
    return class_exists($class, false);
});

<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (file_exists($file = __DIR__.'/../vendor/.composer/autoload.php')) {
    $autoload = require_once $file;
} else {
    throw new RuntimeException('Install dependencies to run test suite.');
}

spl_autoload_register(function($class) {
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

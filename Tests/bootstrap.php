<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$autoload = require_once $file;

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader(function ($class) {
    if (strpos($class, 'FOS\RestBundle\Controller\Annotations\\') === 0) {
        $path = __DIR__.'/../'.str_replace('\\', '/', substr($class, strlen('FOS\RestBundle\\'))).'.php';
        require_once $path;
    }

    return class_exists($class, false);
});

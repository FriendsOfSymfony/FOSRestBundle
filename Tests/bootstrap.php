<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

if (!file_exists($file = __DIR__.'/../vendor/autoload.php')) {
    throw new \RuntimeException('Install the dependencies to run the test suite.');
}

$loader = require $file;
AnnotationRegistry::registerLoader([$loader, 'loadClass']);
AnnotationReader::addGlobalIgnoredName('required');

<?php

require_once './vendor/autoload.php';

use SLLH\StyleCIBridge\ConfigBridge;
use Symfony\CS\Fixer\Contrib\HeaderCommentFixer;

$header = <<<EOF
This file is part of the FOSRestBundle package.

(c) FriendsOfSymfony <http://friendsofsymfony.github.com/>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

// PHP-CS-Fixer 1.x
if (method_exists('Symfony\CS\Fixer\Contrib\HeaderCommentFixer', 'getHeader')) {
    HeaderCommentFixer::setHeader($header);
}

$config = ConfigBridge::create();

// PHP-CS-Fixer 2.x
if (method_exists($config, 'setRules')) {
    $config->setRules(array_merge($config->getRules(), array(
        'header_comment' => array('header' => $header)
    )));
}

return $config;

<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

/**
 * PATCH Route annotation class.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("METHOD")
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD)]
class Patch extends Route
{
    public function getMethod(): string
    {
        return 'PATCH';
    }
}

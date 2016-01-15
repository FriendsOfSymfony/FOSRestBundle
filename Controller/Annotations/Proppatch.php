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
 * PROPPATCH route annotation class (RFC 2518).
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Maximilian Bosch <maximilian.bosch.27@gmail.com>
 */
class Proppatch extends Route
{
    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return 'PROPPATCH';
    }
}

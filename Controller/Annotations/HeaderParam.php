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

use Symfony\Component\HttpFoundation\Request;

/**
 * Represents a parameter that must be present in header.
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Ilia Shcheglov <ilia.sheglov@gmail.com>
 */
class HeaderParam extends AbstractScalarParam
{
    /** @var bool */
    public $strict = true;

    /**
     * {@inheritdoc}
     */
    public function getValue(Request $request, $default = null)
    {
        return $request->headers->get($this->getKey(), $default);
    }
}

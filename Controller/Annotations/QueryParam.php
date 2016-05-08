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
 * Represents a parameter that must be present in GET data.
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class QueryParam extends Param
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Request $request, $default = null)
    {
        return $request->query->get($this->getKey(), $default);
    }
}

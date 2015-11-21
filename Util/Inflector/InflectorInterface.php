<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util\Inflector;

use FOS\RestBundle\Inflector\InflectorInterface as BaseInflectorInterface;

/**
 * Inflector interface.
 *
 * @author Mark Kazemier <Markkaz>
 *
 * @deprecated since 1.7, to be removed in 2.0. Use {@link BaseInflectorInterface} instead.
 */
interface InflectorInterface
{
    /**
     * Pluralizes noun.
     *
     * @param string $word
     *
     * @return string
     */
    public function pluralize($word);
}

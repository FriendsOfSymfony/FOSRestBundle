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

use Doctrine\Common\Inflector\Inflector;

/**
 * Inflector object using the Doctrine/Inflector.
 *
 * @author Mark Kazemier <Markkaz>
 *
 * @deprecated since 1.7, to be remove in 2.0. Use {@link \FOS\RestBundle\Inflector\DoctrineInflector} instead.
 */
class DoctrineInflector implements InflectorInterface
{
    public function __construct()
    {
        @trigger_error(__NAMESPACE__.'\DoctrineInflector is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Inflector\DoctrineInflector instead.', E_USER_DEPRECATED);
    }

    /**
     * {@inheritdoc}
     */
    public function pluralize($word)
    {
        return Inflector::pluralize($word);
    }
}

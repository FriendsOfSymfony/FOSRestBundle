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

use FOS\RestBundle\Inflector\DoctrineInflector as BaseDoctrineInflector;

@trigger_error(__NAMESPACE__.'\DoctrineInflector is deprecated since version 1.7 and will be removed in 2.0. Use FOS\RestBundle\Inflector\DoctrineInflector instead.');

/**
 * Inflector object using the Doctrine/Inflector.
 *
 * @author Mark Kazemier <Markkaz>
 *
 * @deprecated since 1.7, to be remove in 2.0. Use {@link \FOS\RestBundle\Inflector\DoctrineInflector} instead.
 */
class DoctrineInflector extends BaseDoctrineInflector implements InflectorInterface
{
}

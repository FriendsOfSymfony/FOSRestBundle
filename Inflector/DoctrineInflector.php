<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Inflector;

use FOS\RestBundle\Util\Inflector\DoctrineInflector as LegacyDoctrineInflector;

/**
 * Inflector object using the Doctrine/Inflector.
 *
 * @author Mark Kazemier <Markkaz>
 */
class DoctrineInflector extends LegacyDoctrineInflector implements InflectorInterface
{
}

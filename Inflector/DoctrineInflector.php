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

@trigger_error(sprintf('The %s\DoctrineInflector class is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

use Doctrine\Common\Inflector\Inflector;

/**
 * Inflector object using the Doctrine/Inflector.
 *
 * @author Mark Kazemier <Markkaz>
 *
 * @deprecated since 2.8
 */
class DoctrineInflector implements InflectorInterface
{
    /**
     * {@inheritdoc}
     */
    public function pluralize($word)
    {
        return Inflector::pluralize($word);
    }
}

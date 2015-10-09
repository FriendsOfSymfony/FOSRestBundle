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

/**
 * Inflector interface.
 *
 * @author Mark Kazemier <Markkaz>
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

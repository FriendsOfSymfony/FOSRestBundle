<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Normalizer;

/**
 * Normalizes arrays.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
interface ArrayNormalizerInterface
{
    /**
     * Normalizes the array.
     *
     * @param array $data The array to normalize
     *
     * @return array The normalized array
     *
     * @throws Exception\NormalizationException
     */
    public function normalize(array $data);
}

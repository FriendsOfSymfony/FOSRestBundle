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

use FOS\RestBundle\Exception\NormalizedException;

/**
 * Normalizes an exception.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
interface ExceptionNormalizerInterface
{
    /**
     * Normalizes an exception into a set of arrays/scalars.
     *
     * @param object $exception
     *
     * @return NormalizedException
     */
    public function normalize($exception);

    /**
     * Checks whether the given exception is supported for normalization by this normalizer.
     *
     * @param object $exception
     *
     * @return bool
     */
    public function supportsNormalization($exception);
}

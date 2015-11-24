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

class ChainExceptionNormalizer implements ExceptionNormalizerInterface
{
    /**
     * @var ExceptionNormalizerInterface[]
     */
    private $normalizers = array();
    private $sorted;

    /**
     * Adds an exception normalizer.
     *
     * @param ExceptionNormalizerInterface $normalizer
     * @param int                          $priority
     */
    public function addNormalizer(ExceptionNormalizerInterface $normalizer, $priority)
    {
        $this->normalizers[$priority] = $normalizer;
        $this->sorted = null;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($exception)
    {
        $this->sortNormalizers();
        foreach ($this->sorted as $normalizer) {
            if ($normalizer->supportsNormalization($exception)) {
                return $normalizer->normalize($exception);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($exception)
    {
        $this->sortNormalizers();
        foreach ($this->sorted as $normalizer) {
            if ($normalizer->supportsNormalization($exception)) {
                return true;
            }
        }

        return false;
    }

    private function sortNormalizers()
    {
        if (null === $this->sorted) {
            krsort($this->normalizers);
            $this->sorted = $this->normalizers;
        }
    }
}

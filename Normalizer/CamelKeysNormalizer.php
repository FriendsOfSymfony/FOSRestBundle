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

use FOS\RestBundle\Normalizer\Exception\NormalizationException;

/**
 * Normalizes the array by changing its keys from underscore to camel case.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 *
 * @internal
 */
class CamelKeysNormalizer implements ArrayNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(array $data)
    {
        $this->normalizeArray($data);

        return $data;
    }

    private function normalizeArray(array &$data): void
    {
        $normalizedData = [];

        foreach ($data as $key => $val) {
            $normalizedKey = $this->normalizeString($key);

            if ($normalizedKey !== $key) {
                if (array_key_exists($normalizedKey, $normalizedData)) {
                    throw new NormalizationException(sprintf('The key "%s" is invalid as it will override the existing key "%s"', $key, $normalizedKey));
                }
            }

            $normalizedData[$normalizedKey] = $val;
            $key = $normalizedKey;

            if (is_array($val)) {
                $this->normalizeArray($normalizedData[$key]);
            }
        }

        $data = $normalizedData;
    }

    /**
     * Normalizes a string.
     *
     * @return string
     */
    protected function normalizeString(string $string)
    {
        if (false === strpos($string, '_')) {
            return $string;
        }

        return preg_replace_callback('/_([a-zA-Z0-9])/', function ($matches): string {
            return strtoupper($matches[1]);
        }, $string);
    }
}

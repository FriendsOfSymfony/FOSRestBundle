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
 * Abstract Keys Normalizer.
 *
 * @author Oleg Andreyev <oleg@andreyev.lv>
 */
abstract class AbstractKeysNormalizer implements ArrayNormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize(array $data): array
    {
        return $this->normalizeArray($data);
    }

    abstract protected function normalizeString(string $string): string;

    /**
     * Normalizes an array.
     *
     * @param array $data
     *
     * @return array
     * @throws Exception\NormalizationException
     */
    protected function normalizeArray(array $data)
    {
        $normalizedData = [];

        foreach ($data as $key => $val) {
            $normalizedKey = $this->normalizeString($key);

            if (($normalizedKey !== $key) && array_key_exists($normalizedKey, $normalizedData)) {
                throw new NormalizationException(sprintf(
                    'The key "%s" is invalid as it will override the existing key "%s"',
                    $key,
                    $normalizedKey
                ));
            }

            $normalizedData[$normalizedKey] = $val;
            $key = $normalizedKey;

            if (is_array($val)) {
                $normalizedData[$key] = $this->normalizeArray($normalizedData[$key]);
            }
        }

        return $normalizedData;
    }
}

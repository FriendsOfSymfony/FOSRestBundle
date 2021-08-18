<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Form\Transformer;

use Doctrine\Common\Persistence\ObjectManager as LegacyObjectManager;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Class EntityToIdObjectTransformer.
 *
 * @author Marc Juchli <mail@marcjuch.li>
 *
 * @internal
 */
class EntityToIdObjectTransformer implements DataTransformerInterface
{
    private $om;
    private $entityName;

    public function __construct($om, string $entityName)
    {
        if (!$om instanceof ObjectManager && !$om instanceof LegacyObjectManager) {
            throw new \TypeError(sprintf('The first argument of %s() must be an instance of "%s" ("%s" given).', __METHOD__, ObjectManager::class, is_object($om) ? get_class($om) : gettype($om)));
        }

        $this->entityName = $entityName;
        $this->om = $om;
    }

    /**
     * Do nothing.
     *
     * @param object|null $object
     */
    public function transform($object): string
    {
        if (null === $object) {
            return '';
        }

        return current(array_values($this->om->getClassMetadata($this->entityName)->getIdentifierValues($object)));
    }

    /**
     * Transforms an array including an identifier to an object.
     *
     * @param array $idObject
     *
     * @throws TransformationFailedException if object is not found
     */
    public function reverseTransform($idObject): ?object
    {
        if (!is_array($idObject)) {
            return null;
        }

        $identifier = current(array_values($this->om->getClassMetadata($this->entityName)->getIdentifier()));
        $id = $idObject[$identifier];

        $object = $this->om
            ->getRepository($this->entityName)
            ->findOneBy([$identifier => $id]);

        if (null === $object) {
            throw new TransformationFailedException(sprintf('An object with identifier key "%s" and value "%s" does not exist!', $identifier, $id));
        }

        return $object;
    }
}

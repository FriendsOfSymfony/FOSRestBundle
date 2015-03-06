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

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class EntityToIdObjectTransformer
 *
 * @author Marc Juchli <mail@marcjuch.li>
 */
class EntityToIdObjectTransformer implements DataTransformerInterface {

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var String
     */
    private $entityName;

    /**
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, $entityName)
    {
        $this->entityName = $entityName;
        $this->om = $om;
    }

    /**
     * Do nothing.
     *
     * @param  Object|null $object
     * @return Object
     */
    public function transform($object)
    {
        if (null === $object) {
            return "";
        }

        return $object->getId();
    }

    /**
     * Transforms an array including an id to an object.
     *
     * @param  array $idObject
     *
     * @return Object|null
     *
     * @throws TransformationFailedException if object is not found.
     */
    public function reverseTransform($idObject)
    {
        if (!$idObject || !$idObject['id']) {
            return null;
        }

        $id = $idObject['id'];

        $object = $this->om
            ->getRepository($this->entityName)
            ->findOneBy(array('id' => $id))
        ;

        if (null === $object) {
            throw new TransformationFailedException(sprintf(
                'An object with id "%s" does not exist!',
                $id
            ));
        }

        return $object;
    }
} 
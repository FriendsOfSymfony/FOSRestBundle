<?php

declare(strict_types=1);

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request\Pagination;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Bartek Chmura <bartek@nuvola.pl>
 */
final class PaginationParamConverter implements ParamConverterInterface
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var ValidatorInterface|null
     */
    private $validator;

    public function __construct(DenormalizerInterface $denormalizer, ?ValidatorInterface $validator = null)
    {
        $this->denormalizer = $denormalizer;
        $this->validator    = $validator;
    }

    public function apply(Request $request, ?ParamConverter $configuration)
    {
        $name = $configuration->getName();

        if (false === $request->query->has($name)) {
            return false;
        }

        $pagination = $this->denormalizer->denormalize(
            $request->query->all($name),
            $configuration->getClass()
        );

        $request->attributes->set($name, $pagination);

        if (null === $this->validator) {
            return true;
        }

        $errors = $this->validator->validate($pagination);

        if ($errors->count()) {
            $request->attributes->set('errors', $errors);
        }

        return true;
    }

    public function supports(ParamConverter $configuration)
    {
        if ('fos_rest.pagination' !== $configuration->getConverter()) {
            return false;
        }

        if (\is_a($configuration->getClass(), PaginationInterface::class, true)) {
            return true;
        }

        if (null === $configuration->getClass()) {
            return false;
        }

        return true;
    }
}

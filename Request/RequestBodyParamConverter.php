<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use FOS\RestBundle\Context\Adapter\DeserializationContextAdapterInterface;
use FOS\RestBundle\Context\Adapter\SerializerAwareInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Context\ContextInterface;
use FOS\RestBundle\Context\GroupableContextInterface;
use FOS\RestBundle\Context\MaxDepthContextInterface;
use FOS\RestBundle\Context\SerializeNullContextInterface;
use FOS\RestBundle\Context\VersionableContextInterface;
use JMS\Serializer\Exception\Exception as JMSSerializerException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SymfonySerializerException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverter implements ParamConverterInterface
{
    private $serializer;
    private $context = [];
    private $validator;

    /**
     * The name of the argument on which the ConstraintViolationList will be set.
     *
     * @var null|string
     */
    private $validationErrorsArgument;

    /**
     * @var DeserializationContextAdapterInterface
     */
    private $contextAdapter;

    /**
     * @param object             $serializer
     * @param array|null         $groups                   An array of groups to be used in the serialization context
     * @param string|null        $version                  A version string to be used in the serialization context
     * @param ValidatorInterface $validator
     * @param string|null        $validationErrorsArgument
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $serializer,
        $groups = null,
        $version = null,
        ValidatorInterface $validator = null,
        $validationErrorsArgument = null
    ) {
        $this->serializer = $serializer;

        if (!empty($groups)) {
            $this->context['groups'] = (array) $groups;
        }

        if (!empty($version)) {
            $this->context['version'] = $version;
        }

        if (null !== $validator && null === $validationErrorsArgument) {
            throw new \InvalidArgumentException('"$validationErrorsArgument" cannot be null when using the validator');
        }

        $this->validator = $validator;
        $this->validationErrorsArgument = $validationErrorsArgument;
    }

    /**
     * Sets context adapter.
     *
     * @param DeserializationContextAdapterInterface $contextAdapter
     */
    public function setDeserializationContextAdapter(DeserializationContextAdapterInterface $contextAdapter)
    {
        $this->contextAdapter = $contextAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array) $configuration->getOptions();

        if (isset($options['deserializationContext']) && is_array($options['deserializationContext'])) {
            $context = array_merge($this->context, $options['deserializationContext']);
        } else {
            $context = $this->context;
        }

        $context = $this->configureDeserializationContext($this->getDeserializationContext(), $context);
        if ($this->contextAdapter instanceof SerializerAwareInterface) {
            $this->contextAdapter->setSerializer($this->serializer);
        }
        $context = $this->contextAdapter->convertDeserializationContext($context);

        try {
            $object = $this->serializer->deserialize(
                $request->getContent(),
                $configuration->getClass(),
                $request->getContentType(),
                $context
            );
        } catch (UnsupportedFormatException $e) {
            throw new UnsupportedMediaTypeHttpException($e->getMessage(), $e);
        } catch (JMSSerializerException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (SymfonySerializerException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $request->attributes->set($configuration->getName(), $object);

        if (null !== $this->validator) {
            $validatorOptions = $this->getValidatorOptions($options);

            $errors = $this->validator->validate($object, null, $validatorOptions['groups']);

            $request->attributes->set(
                $this->validationErrorsArgument,
                $errors
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return null !== $configuration->getClass();
    }

    /**
     * @return ContextInterface
     */
    protected function getDeserializationContext()
    {
        return new Context();
    }

    /**
     * @param ContextInterface $context
     * @param array            $options
     *
     * @return ContextInterface
     */
    protected function configureDeserializationContext(ContextInterface $context, array $options)
    {
        foreach ($options as $key => $value) {
            if ($key == 'groups' && $context instanceof GroupableContextInterface) {
                $context->addGroups($options['groups']);
            } elseif ($key == 'version' && $context instanceof VersionableContextInterface) {
                $context->setVersion($options['version']);
            } elseif ($key == 'maxDepth' && $context instanceof MaxDepthContextInterface) {
                $context->setMaxDepth($options['maxDepth']);
            } elseif ($key == 'serializeNull' && $context instanceof SerializeNullContextInterface) {
                $context->setSerializeNull($options['serializeNull']);
            } else {
                $context->setAttribute($key, $value);
            }
        }

        return $context;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function getValidatorOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'groups' => null,
            'traverse' => false,
            'deep' => false,
        ]);

        return $resolver->resolve(isset($options['validator']) ? $options['validator'] : []);
    }
}

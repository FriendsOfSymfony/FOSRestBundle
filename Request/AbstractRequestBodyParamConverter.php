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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\Exception as JMSSerializerException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use JMS\Serializer\SerializerInterface as JMSSerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Exception\Exception as SymfonySerializerException;
use Symfony\Component\Validator\ValidatorInterface as LegacyValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
abstract class AbstractRequestBodyParamConverter implements ParamConverterInterface
{
    protected $serializer;
    protected $context = array();
    protected $validator;

    /**
     * The name of the argument on which the ConstraintViolationList will be set.
     *
     * @var null|string
     */
    protected $validationErrorsArgument;

    /**
     * @param object                                      $serializer
     * @param array|null                                  $groups                   An array of groups to be used in the serialization context
     * @param string|null                                 $version                  A version string to be used in the serialization context
     * @param object                                      $serializer
     * @param LegacyValidatorInterface|ValidatorInterface $validator
     * @param string|null                                 $validationErrorsArgument
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $serializer,
        $groups = null,
        $version = null,
        $validator = null,
        $validationErrorsArgument = null
    ) {
        $this->serializer = $serializer;

        if (!empty($groups)) {
            $this->context['groups'] = (array) $groups;
        }

        if (!empty($version)) {
            $this->context['version'] = $version;
        }

        if ($validator !== null && !$validator instanceof LegacyValidatorInterface && !$validator instanceof ValidatorInterface) {
            throw new \InvalidArgumentException(sprintf(
                'Validator has expected to be an instance of %s or %s, "%s" given',
                'Symfony\Component\Validator\ValidatorInterface',
                'Symfony\Component\Validator\Validator\ValidatorInterface',
                get_class($validator)
            ));
        }

        if (null !== $validator && null === $validationErrorsArgument) {
            throw new \InvalidArgumentException('"$validationErrorsArgument" cannot be null when using the validator');
        }

        $this->validator = $validator;
        $this->validationErrorsArgument = $validationErrorsArgument;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request        $request       The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     *
     * @throws UnsupportedMediaTypeHttpException
     * @throws BadRequestHttpException
     */
    protected function execute(Request $request, ParamConverter $configuration)
    {
        $options = (array) $configuration->getOptions();

        if (isset($options['deserializationContext']) && is_array($options['deserializationContext'])) {
            $arrayContext = array_merge($this->context, $options['deserializationContext']);
        } else {
            $arrayContext = $this->context;
        }

        if ($this->serializer instanceof JMSSerializerInterface || $this->serializer instanceof Serializer) {
            // BC < 1.8
            if (get_class($this) === 'FOS\RestBundle\Request\RequestBodyParamConverter' || get_class($this) === 'FOS\RestBundle\Request\RequestBodyParamConverter20') {
                $context = new Context();
                $this->configureContext($context, $arrayContext);
            } else {
                $method = new \ReflectionMethod($this, 'getDeserializationContext');
                if ($method->getDeclaringClass()->getName() !== __CLASS__) {
                    $context = $this->configureDeserializationContext($this->getDeserializationContext(), $arrayContext);
                } else {
                    $context = new Context();
                    $this->configureContext($context, $arrayContext);
                }
            }
        } else {
            $context = $arrayContext;
        }

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

            if ($this->validator instanceof ValidatorInterface) {
                $errors = $this->validator->validate($object, null, $validatorOptions['groups']);
            } else {
                $errors = $this->validator->validate(
                    $object,
                    $validatorOptions['groups'],
                    $validatorOptions['traverse'],
                    $validatorOptions['deep']
                );
            }

            $request->attributes->set(
                $this->validationErrorsArgument,
                $errors
            );
        }

        return true;
    }

    /**
     * @return DeserializationContext
     *
     * @deprecated since 1.8, to be removed in 2.0. Use {@link AbstractRequestBodyParamConverter::configureContext()} instead.
     */
    protected function getDeserializationContext()
    {
        @trigger_error(sprintf('%s is deprecated since version 1.8 and will be removed in 2.0. Use %s::configureContext() instead.', __METHOD__, get_class($this)), E_USER_DEPRECATED);

        return DeserializationContext::create();
    }

    /**
     * @param Context $context
     * @param array   $options
     */
    protected function configureContext(Context $context, array $options)
    {
        if (isset($options['groups'])) {
            $context->addGroups($options['groups']);
        }
        if (isset($options['version'])) {
            $context->setVersion($options['version']);
        }
    }

    /**
     * @param DeserializationContext $context
     * @param array                  $options
     *
     * @return DeserializationContext
     *
     * @deprecated since 1.8, to be removed in 2.0. Use {@link AbstractRequestBodyParamConverter::configureContext()} instead.
     */
    protected function configureDeserializationContext(DeserializationContext $context, array $options)
    {
        @trigger_error(sprintf('%s is deprecated since version 1.8 and will be removed in 2.0. Use %s::configureContext() instead.', __METHOD__, get_class($this)), E_USER_DEPRECATED);

        if (isset($options['groups'])) {
            $context->setGroups($options['groups']);
        }
        if (isset($options['version'])) {
            $context->setVersion($options['version']);
        }

        return $context;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getValidatorOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'groups' => null,
            'traverse' => false,
            'deep' => false,
        ));

        return $resolver->resolve(isset($options['validator']) ? $options['validator'] : array());
    }
}

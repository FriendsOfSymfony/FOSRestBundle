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
 *
 * @final since 2.8
 */
class RequestBodyParamConverter implements ParamConverterInterface
{
    private $serializer;
    private $context = [];
    private $validator;
    private $validationErrorsArgument;

    /**
     * @param string[]|null $groups  An array of groups to be used in the serialization context
     * @param string|null   $version A version string to be used in the serialization context
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        Serializer $serializer,
        ?array $groups = null,
        ?string $version = null,
        ValidatorInterface $validator = null,
        ?string $validationErrorsArgument = null
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
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $options = (array) $configuration->getOptions();

        if (isset($options['deserializationContext']) && is_array($options['deserializationContext'])) {
            $arrayContext = array_merge($this->context, $options['deserializationContext']);
        } else {
            $arrayContext = $this->context;
        }
        $this->configureContext($context = new Context(), $arrayContext);

        $format = $request->getContentType();
        if (null === $format) {
            return $this->throwException(new UnsupportedMediaTypeHttpException(), $configuration);
        }

        try {
            $object = $this->serializer->deserialize(
                $request->getContent(),
                $configuration->getClass(),
                $format,
                $context
            );
        } catch (UnsupportedFormatException $e) {
            return $this->throwException(new UnsupportedMediaTypeHttpException($e->getMessage(), $e), $configuration);
        } catch (JMSSerializerException $e) {
            return $this->throwException(new BadRequestHttpException($e->getMessage(), $e), $configuration);
        } catch (SymfonySerializerException $e) {
            return $this->throwException(new BadRequestHttpException($e->getMessage(), $e), $configuration);
        }

        $request->attributes->set($configuration->getName(), $object);

        if (null !== $this->validator && (!isset($options['validate']) || $options['validate'])) {
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
        return null !== $configuration->getClass() && 'fos_rest.request_body' === $configuration->getConverter();
    }

    protected function configureContext(Context $context, array $options)
    {
        foreach ($options as $key => $value) {
            if ('groups' === $key) {
                $context->addGroups($options['groups']);
            } elseif ('version' === $key) {
                $context->setVersion($options['version']);
            } elseif ('maxDepth' === $key) {
                @trigger_error('Context attribute "maxDepth" is deprecated since version 2.1 and will be removed in 3.0. Use "enable_max_depth" instead.', E_USER_DEPRECATED);
                $context->setMaxDepth($options['maxDepth']);
            } elseif ('enableMaxDepth' === $key) {
                $context->enableMaxDepth($options['enableMaxDepth']);
            } elseif ('serializeNull' === $key) {
                $context->setSerializeNull($options['serializeNull']);
            } else {
                $context->setAttribute($key, $value);
            }
        }
    }

    private function throwException(\Exception $exception, ParamConverter $configuration)
    {
        if ($configuration->isOptional()) {
            return false;
        }

        throw $exception;
    }

    private function getValidatorOptions(array $options): array
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

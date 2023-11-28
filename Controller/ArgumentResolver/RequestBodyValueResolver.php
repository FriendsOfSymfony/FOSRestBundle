<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\ArgumentResolver;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations\MapRequestBody;
use FOS\RestBundle\Serializer\Serializer;
use JMS\Serializer\Exception\Exception as JMSSerializerException;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SymfonySerializerException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

if (interface_exists(ValueResolverInterface::class)) {
    /**
     * Compat value resolver for Symfony 6.2 and newer.
     *
     * @internal
     */
    abstract class CompatRequestBodyValueResolver implements ValueResolverInterface {}
} else {
    /**
     * Compat value resolver for Symfony 6.1 and older.
     *
     * @internal
     */
    abstract class CompatRequestBodyValueResolver implements ArgumentValueResolverInterface
    {
        public function supports(Request $request, ArgumentMetadata $argument): bool
        {
            $attribute = $argument->getAttributesOfType(MapRequestBody::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;

            return $attribute instanceof MapRequestBody;
        }
    }
}

final class RequestBodyValueResolver extends CompatRequestBodyValueResolver implements EventSubscriberInterface
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array<string, mixed>
     */
    private $context = [];

    /**
     * @var ValidatorInterface|null
     */
    private $validator;

    /**
     * @param list<string>|null $groups
     */
    public function __construct(
        Serializer $serializer,
        ?array $groups = null,
        ?string $version = null,
        ?ValidatorInterface $validator = null
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;

        if (!empty($groups)) {
            $this->context['groups'] = (array) $groups;
        }

        if (!empty($version)) {
            $this->context['version'] = $version;
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments',
        ];
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(MapRequestBody::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;

        if (!$attribute) {
            return [];
        }

        if ($argument->isVariadic()) {
            throw new \LogicException(sprintf('Mapping variadic argument "$%s" is not supported.', $argument->getName()));
        }

        $attribute->metadata = $argument;

        return [$attribute];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $arguments = $event->getArguments();

        foreach ($arguments as $i => $argument) {
            if (!$argument instanceof MapRequestBody) {
                continue;
            }

            if (!$type = $argument->metadata->getType()) {
                throw new \LogicException(sprintf('Could not resolve the "$%s" controller argument: argument should be typed.', $argument->metadata->getName()));
            }

            $request = $event->getRequest();

            $format = method_exists(Request::class, 'getContentTypeFormat') ? $request->getContentTypeFormat() : $request->getContentType();

            if (null === $format) {
                throw new UnsupportedMediaTypeHttpException('Unsupported format.');
            }

            try {
                $payload = $this->serializer->deserialize(
                    $request->getContent(),
                    $type,
                    $format,
                    $this->createContext(array_merge($this->context, $argument->deserializationContext))
                );
            } catch (UnsupportedFormatException $e) {
                throw new UnsupportedMediaTypeHttpException($e->getMessage(), $e);
            } catch (JMSSerializerException|SymfonySerializerException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            }

            if (null !== $payload && null !== $this->validator && $argument->validate) {
                $validatorOptions = $this->getValidatorOptions($argument);

                $violations = $this->validator->validate($payload, null, $validatorOptions['groups']);

                if (\count($violations)) {
                    throw new UnprocessableEntityHttpException(
                        implode("\n", array_map(static function ($e) { return $e->getMessage(); }, iterator_to_array($violations))),
                        new ValidationFailedException($payload, $violations)
                    );
                }
            }

            if (null === $payload) {
                if ($argument->metadata->hasDefaultValue()) {
                    $payload = $argument->metadata->getDefaultValue();
                } elseif ($argument->metadata->isNullable()) {
                    $payload = null;
                } else {
                    throw new UnprocessableEntityHttpException();
                }
            }

            $arguments[$i] = $payload;
        }

        $event->setArguments($arguments);
    }

    private function createContext(array $options): Context
    {
        $context = new Context();

        foreach ($options as $key => $value) {
            if ('groups' === $key) {
                $context->addGroups($options['groups']);
            } elseif ('version' === $key) {
                $context->setVersion($options['version']);
            } elseif ('enableMaxDepth' === $key) {
                if (true === $options['enableMaxDepth']) {
                    $context->enableMaxDepth();
                } elseif (false === $options['enableMaxDepth']) {
                    $context->disableMaxDepth();
                }
            } elseif ('serializeNull' === $key) {
                $context->setSerializeNull($options['serializeNull']);
            } else {
                $context->setAttribute($key, $value);
            }
        }

        return $context;
    }

    private function getValidatorOptions(MapRequestBody $argument): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'groups' => null,
            'traverse' => false,
            'deep' => false,
        ]);

        return $resolver->resolve($argument->validator);
    }
}

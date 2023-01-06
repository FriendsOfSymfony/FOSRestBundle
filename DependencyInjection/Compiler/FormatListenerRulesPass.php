<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\AttributesRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\HostRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\MethodRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;

/**
 * @author Eduardo Gulias Davis <me@egulias.com>
 *
 * @internal
 */
final class FormatListenerRulesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('fos_rest.format_listener')) {
            return;
        }

        if ($container->hasParameter('web_profiler.debug_toolbar.mode')) {
            $path = '_profiler';
            if (2 === $container->getParameter('web_profiler.debug_toolbar.mode')) {
                $path .= '|_wdt';
            }

            $profilerRule = [
                'host' => null,
                'methods' => null,
                'path' => "^/$path/",
                'priorities' => ['html', 'json'],
                'fallback_format' => 'html',
                'attributes' => [],
                'prefer_extension' => true,
            ];

            $this->addRule($profilerRule, $container);
        }

        $rules = $container->getParameter('fos_rest.format_listener.rules');
        foreach ($rules as $rule) {
            $this->addRule($rule, $container);
        }

        $container->setParameter('fos_rest.format_listener.rules', null);
    }

    private function addRule(array $rule, ContainerBuilder $container): void
    {
        $matcher = $this->createRequestMatcher(
            $container,
            $rule['path'],
            $rule['host'],
            $rule['methods'],
            $rule['attributes']
        );

        unset($rule['path'], $rule['host']);
        if (is_bool($rule['prefer_extension']) && $rule['prefer_extension']) {
            $rule['prefer_extension'] = '2.0';
        }

        $container->getDefinition('fos_rest.format_negotiator')
            ->addMethodCall('add', [$matcher, $rule]);
    }

    private function createRequestMatcher(ContainerBuilder $container, ?string $path = null, ?string $host = null, ?array $methods = null, array $attributes = []): Reference
    {
        $arguments = [$path, $host, $methods, null, $attributes];
        $serialized = serialize($arguments);
        $id = 'fos_rest.request_matcher.'.md5($serialized).sha1($serialized);

        if (!$container->hasDefinition($id)) {
            // only add arguments that are necessary
            if (!class_exists(ChainRequestMatcher::class)) {
                $container->setDefinition($id, new Definition(RequestMatcher::class, $arguments));
            } else {
                $matchers = [];
                if (!is_null($path)) {
                    $matchers[] = new Definition(PathRequestMatcher::class, [$path]);
                }
                if (!is_null($host)) {
                    $matchers[] = new Definition(HostRequestMatcher::class, [$host]);
                }
                if (!is_null($methods)) {
                    $matchers[] = new Definition(MethodRequestMatcher::class, [$methods]);
                }
                if ([] !== $attributes) {
                    $matchers[] = new Definition(AttributesRequestMatcher::class, [$attributes]);
                }
                $container
                    ->setDefinition($id, new Definition(ChainRequestMatcher::class))
                    ->setArguments([$matchers]);
            }
        }

        return new Reference($id);
    }
}

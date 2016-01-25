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
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Eduardo Gulias Davis <me@egulias.com>
 *
 * @internal
 */
final class FormatListenerRulesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
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
                'exception_fallback_format' => 'html',
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

    private function addRule(array $rule, ContainerBuilder $container)
    {
        $matcher = $this->createRequestMatcher(
            $container,
            $rule['path'],
            $rule['host'],
            $rule['methods']
        );

        unset($rule['path'], $rule['host']);
        if (is_bool($rule['prefer_extension']) && $rule['prefer_extension']) {
            $rule['prefer_extension'] = '2.0';
        }

        $exceptionFallbackFormat = $rule['exception_fallback_format'];
        unset($rule['exception_fallback_format']);
        $container->getDefinition('fos_rest.format_negotiator')
            ->addMethodCall('add', [$matcher, $rule]);

        if ($container->hasDefinition('fos_rest.exception_format_negotiator')) {
            $rule['fallback_format'] = $exceptionFallbackFormat;
            $container->getDefinition('fos_rest.exception_format_negotiator')
                ->addMethodCall('add', [$matcher, $rule]);
        }
    }

    private function createRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = null)
    {
        $arguments = [$path, $host, $methods];
        $serialized = serialize($arguments);
        $id = 'fos_rest.request_matcher.'.md5($serialized).sha1($serialized);

        if (!$container->hasDefinition($id)) {
            // only add arguments that are necessary
            $container
                ->setDefinition($id, new DefinitionDecorator('fos_rest.format_request_matcher'))
                ->setArguments($arguments);
        }

        return new Reference($id);
    }
}

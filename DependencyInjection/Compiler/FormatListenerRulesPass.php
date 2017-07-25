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

use Symfony\Component\DependencyInjection\ChildDefinition;
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
                'attributes' => array(),
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

    private function createRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = null, array $attributes = array())
    {
        $arguments = [$path, $host, $methods, null, $attributes];
        $serialized = serialize($arguments);
        $id = 'fos_rest.request_matcher.'.md5($serialized).sha1($serialized);

        if (!$container->hasDefinition($id)) {
            $childDefinitionClass = class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
            // only add arguments that are necessary
            $container
                ->setDefinition($id, new $childDefinitionClass('fos_rest.format_request_matcher'))
                ->setArguments($arguments);
        }

        return new Reference($id);
    }
}

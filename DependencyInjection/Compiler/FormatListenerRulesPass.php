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
 * Class FormatListenerRulesPass
 * @author Eduardo Gulias Davis <me@egulias.com>
 */
class FormatListenerRulesPass implements CompilerPassInterface
{
    private $alias;

    public function __construct($alias = 'fos_rest')
    {
        $this->alias = $alias;
    }

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->alias.'.format_listener')) {
            return;
        }

        $fosRestConfig = $container->getExtensionConfig($this->alias);
        $profilerConfig = $container->getExtensionConfig('web_profiler');

        $devRules = $this->getDevelopmentRules($fosRestConfig, $profilerConfig);
        $fosRestConfig[0]['format_listener']['rules'] = array_merge(
            $fosRestConfig[0]['format_listener']['rules'],
            $devRules
        );

        foreach ($fosRestConfig[0]['format_listener']['rules'] as $rule) {
            $this->addRule($rule, $container);
        }
    }

    protected function addRule($rule, $container)
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

        $container->getDefinition($this->alias.'.format_negotiator')
            ->addMethodCall('add', array($matcher, $rule));
    }

    protected function getDevelopmentRules($fosRestConfig, $profilerConfig)
    {
        if (!isset($fosRestConfig[0]['format_listener']) && !isset($profilerConfig[0]['toolbar'])) {
            return array();
        }

        if (!$profilerConfig[0]['toolbar']) {
            return array();
        }

        $rules[] = array(
            'host' => null,
            'methods' => null,
            'path' => '^/api/',
            'priorities' => array('html', 'json', 'xml'),
            'fallback_format' => 'html',
            'prefer_extension' => true
        );
        $rules[] = array(
            'host' => null,
            'methods' => null,
            'path' => '^/',
            'priorities' => array('html', '*/*'),
            'fallback_format' => 'html',
            'prefer_extension' => true
        );

        return $rules;
    }

    protected function createRequestMatcher(ContainerBuilder $container, $path = null, $host = null, $methods = null)
    {
        $arguments = array($path, $host, $methods);
        $serialized = serialize($arguments);
        $id = $this->alias.'.request_matcher.'.md5($serialized).sha1($serialized);

        if (!$container->hasDefinition($id)) {
            // only add arguments that are necessary
            $container
                ->setDefinition($id, new DefinitionDecorator($this->alias.'.request_matcher'))
                ->setArguments($arguments)
            ;
        }

        return new Reference($id);
    }
}

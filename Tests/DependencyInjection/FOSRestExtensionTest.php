<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\DependencyInjection;

use FOS\RestBundle\DependencyInjection\Configuration;
use FOS\RestBundle\DependencyInjection\FOSRestExtension;
use FOS\RestBundle\EventListener\ZoneMatcherListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;

/**
 * FOSRestExtension test.
 *
 * @author Bulat Shakirzyanov <avalanche123>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class FOSRestExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var FOSRestExtension
     */
    private $extension;

    /**
     * @var bool
     */
    private $includeFormat;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var string
     */
    private $defaultFormat;

    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', ['JMSSerializerBundle' => true]);
        $this->container->setParameter('kernel.debug', false);
        $this->extension = new FOSRestExtension();
        $this->includeFormat = true;
        $this->formats = [
            'json' => false,
            'xml' => false,
        ];
        $this->defaultFormat = null;
    }

    protected function tearDown(): void
    {
        unset($this->container, $this->extension);
    }

    public function testDisableBodyListener(): void
    {
        $config = [
            'fos_rest' => [
                'body_listener' => false,
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.body_listener'));
    }

    public function testLoadBodyListenerWithDefaults(): void
    {
        $this->extension->load([
            'fos_rest' => [
                'body_listener' => true,
            ],
        ], $this->container);
        $decoders = [
            'json' => 'fos_rest.decoder.json',
            'xml' => 'fos_rest.decoder.xml',
        ];

        $this->assertTrue($this->container->hasDefinition('fos_rest.body_listener'));
        $this->assertEquals($decoders, $this->container->getDefinition('fos_rest.decoder_provider')->getArgument(1));
        $this->assertFalse($this->container->getDefinition('fos_rest.body_listener')->getArgument(1));
        $this->assertCount(2, $this->container->getDefinition('fos_rest.body_listener')->getArguments());
    }

    public function testLoadBodyListenerWithNormalizerString(): void
    {
        $config = [
            'fos_rest' => [
                'body_listener' => [
                    'enabled' => true,
                    'array_normalizer' => 'fos_rest.normalizer.camel_keys',
                ],
            ],
        ];

        $this->extension->load($config, $this->container);
        $normalizerArgument = $this->container->getDefinition('fos_rest.body_listener')->getArgument(2);

        $this->assertInstanceOf(Reference::class, $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
    }

    public function testLoadBodyListenerWithNormalizerArray(): void
    {
        $config = [
            'fos_rest' => [
                'body_listener' => [
                    'enabled' => true,
                    'array_normalizer' => [
                        'service' => 'fos_rest.normalizer.camel_keys',
                    ],
                ],
            ],
        ];

        $this->extension->load($config, $this->container);
        $bodyListener = $this->container->getDefinition('fos_rest.body_listener');
        $normalizerArgument = $bodyListener->getArgument(2);
        $normalizeForms = $bodyListener->getArgument(3);

        $this->assertCount(4, $bodyListener->getArguments());
        $this->assertInstanceOf(Reference::class, $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
        $this->assertFalse($normalizeForms);
    }

    public function testLoadBodyListenerWithNormalizerArrayAndForms(): void
    {
        $config = [
            'fos_rest' => [
                'body_listener' => [
                    'enabled' => true,
                    'array_normalizer' => [
                        'service' => 'fos_rest.normalizer.camel_keys',
                        'forms' => true,
                    ],
                ],
            ],
        ];

        $this->extension->load($config, $this->container);
        $bodyListener = $this->container->getDefinition('fos_rest.body_listener');
        $normalizerArgument = $bodyListener->getArgument(2);
        $normalizeForms = $bodyListener->getArgument(3);

        $this->assertCount(4, $bodyListener->getArguments());
        $this->assertInstanceOf(Reference::class, $normalizerArgument);
        $this->assertEquals('fos_rest.normalizer.camel_keys', (string) $normalizerArgument);
        $this->assertTrue($normalizeForms);
    }

    public function testDisableFormatListener(): void
    {
        $config = [
            'fos_rest' => [
                'format_listener' => false,
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerWithDefaults(): void
    {
        $this->extension->load([
            'fos_rest' => [],
        ], $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerWithSingleRule(): void
    {
        $config = [
            'fos_rest' => [
                'format_listener' => [
                    'rules' => ['path' => '/'],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadParamFetcherListener(): void
    {
        $config = [
            'fos_rest' => [
                'param_fetcher_listener' => true,
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.param_fetcher_listener'));
        $this->assertFalse($this->container->getDefinition('fos_rest.param_fetcher_listener')->getArgument(1));
    }

    public function testLoadParamFetcherListenerForce(): void
    {
        $config = [
            'fos_rest' => [
                'param_fetcher_listener' => 'force',
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.param_fetcher_listener'));
        $this->assertTrue($this->container->getDefinition('fos_rest.param_fetcher_listener')->getArgument(1));
    }

    public function testLoadFormatListenerWithMultipleRule(): void
    {
        $config = [
            'fos_rest' => [
                'format_listener' => [
                    'rules' => [
                        ['path' => '/foo'],
                        ['path' => '/'],
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.format_listener'));
    }

    public function testLoadFormatListenerMediaTypeNoRules(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $config = [
            'fos_rest' => [
                'format_listener' => [
                    'media_type' => true,
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
    }

    public function testLoadServicesWithDefaults(): void
    {
        $this->extension->load([
            'fos_rest' => [],
        ], $this->container);

        $this->assertAlias('fos_rest.view_handler.default', 'fos_rest.view_handler');

        $viewHandlerAlias = $this->container->getAlias('fos_rest.view_handler');

        $this->assertTrue($viewHandlerAlias->isPublic());
        $this->assertFalse($viewHandlerAlias->isPrivate());
    }

    public function testDisableViewResponseListener(): void
    {
        $config = [
            'fos_rest' => [
                'view' => [
                    'view_response_listener' => false,
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.view_response_listener'));
    }

    public function testLoadViewResponseListener(): void
    {
        $config = [
            'fos_rest' => [
                'view' => [
                    'view_response_listener' => true,
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.view_response_listener'));
        $this->assertFalse($this->container->getDefinition('fos_rest.view_response_listener')->getArgument(1));
    }

    public function testLoadViewResponseListenerForce(): void
    {
        $config = [
            'fos_rest' => [
                'view' => [
                    'view_response_listener' => 'force',
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.view_response_listener'));
        $this->assertTrue($this->container->getDefinition('fos_rest.view_response_listener')->getArgument(1));
    }

    public function testForceEmptyContentDefault(): void
    {
        $this->extension->load([
           'fos_rest' => [],
        ], $this->container);
        $this->assertEquals(204, $this->container->getDefinition('fos_rest.view_handler.default')->getArgument(5));
    }

    public function testForceEmptyContentIs200(): void
    {
        $config = [
            'fos_rest' => [
                'view' => [
                    'empty_content' => 200,
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->assertEquals(200, $this->container->getDefinition('fos_rest.view_handler.default')->getArgument(5));
    }

    public function testViewSerializeNullDefault(): void
    {
        $this->extension->load([
            'fos_rest' => [],
        ], $this->container);
        $this->assertFalse($this->container->getDefinition('fos_rest.view_handler.default')->getArgument(6));
    }

    public function testViewSerializeNullIsTrue(): void
    {
        $config = [
            'fos_rest' => [
                'view' => [
                    'serialize_null' => true,
                ],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->assertTrue($this->container->getDefinition('fos_rest.view_handler.default')->getArgument(6));
    }

    public function testViewHandlerSerializerOptions(): void
    {
        $config = [
            'fos_rest' => [
                'serializer' => [
                    'groups' => ['foo', 'bar'],
                    'serialize_null' => true,
                    'version' => '1.0',
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertEquals([
            'exclusionStrategyGroups' => ['foo', 'bar'],
            'exclusionStrategyVersion' => '1.0',
            'serializeNullStrategy' => true,
        ], $this->container->getDefinition('fos_rest.view_handler.default')->getArgument(7));
    }

    public function testValidatorAliasWhenEnabled(): void
    {
        $config = [
            'fos_rest' => [
                'body_converter' => ['validate' => true],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->assertAlias('validator', 'fos_rest.validator');
    }

    public function testValidatorAliasWhenDisabled(): void
    {
        $config = [
            'fos_rest' => [
                'body_converter' => ['validate' => false],
            ],
        ];
        $this->extension->load($config, $this->container);
        $this->assertFalse($this->container->has('fos_rest.validator'));
    }

    public function testLoadOkMessagesClass(): void
    {
        $this->extension->load([
            'fos_rest' => [
                'exception' => [
                    'codes' => [
                        'Exception' => 404,
                    ],
                ],
            ],
        ], $this->container);
        $this->assertFalse($this->container->hasDefinition('fos_rest.exception.codes'));
    }

    /**
     * @dataProvider getLoadBadCodeValueThrowsExceptionData
     */
    public function testLoadBadCodeValueThrowsException($value): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid HTTP code in fos_rest.exception.codes');

        $this->extension->load([
            'fos_rest' => [
                'exception' => [
                    'codes' => [
                        'Exception' => $value,
                    ],
                ],
            ],
        ], $this->container);
    }

    public function getLoadBadCodeValueThrowsExceptionData(): array
    {
        $data = [
            null,
            'HTTP_NOT_EXISTS',
            'some random string',
            true,
        ];

        return array_map(function ($i): array {
            return [$i];
        }, $data);
    }

    public function testResponseStatusCodeListenerEnabled(): void
    {
        $extension = new FOSRestExtension();
        $extension->load([
            [
                'exception' => [
                    'map_exception_codes' => true,
                ],
            ],
        ], $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.exception.response_status_code_listener'));
    }

    public function testExceptionListenerDisabled(): void
    {
        $extension = new FOSRestExtension();
        $extension->load([], $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.fos_rest.error_listener'));
    }

    public function testGetConfiguration(): void
    {
        $configuration = $this->extension->getConfiguration([], $this->container);

        $this->assertInstanceOf(Configuration::class, $configuration);
    }

    private function assertAlias(string $value, string $key): void
    {
        $this->assertEquals($value, (string) $this->container->getAlias($key), sprintf('%s alias is correct', $key));
    }

    public function testCheckViewHandlerWithJsonp(): void
    {
        $this->extension->load([
            'fos_rest' => [
                'view' => [
                    'jsonp_handler' => null,
                ],
            ],
        ], $this->container);

        $this->assertTrue($this->container->has('fos_rest.view_handler'));

        $viewHandler = $this->container->getDefinition('fos_rest.view_handler');

        $this->assertInstanceOf(ChildDefinition::class, $viewHandler);
    }

    public function testZoneMatcherListenerDefault(): void
    {
        $this->extension->load([
            'fos_rest' => [],
        ], $this->container);

        $this->assertFalse($this->container->has('fos_rest.zone_matcher_listener'));
    }

    public function testZoneMatcherListener(): void
    {
        $config = ['fos_rest' => [
            'zone' => [
                'first' => ['path' => '/api/*'],
                'second' => ['path' => '/^second', 'ips' => '127.0.0.1'],
            ],
        ]];

        $this->extension->load($config, $this->container);
        $zoneMatcherListener = $this->container->getDefinition('fos_rest.zone_matcher_listener');
        $addRequestMatcherCalls = $zoneMatcherListener->getMethodCalls();

        $this->assertTrue($this->container->has('fos_rest.zone_matcher_listener'));
        $this->assertEquals(ZoneMatcherListener::class, $zoneMatcherListener->getClass());
        $this->assertCount(2, $addRequestMatcherCalls);

        // First zone
        $this->assertEquals('addRequestMatcher', $addRequestMatcherCalls[0][0]);
        $requestMatcherFirstId = (string) $addRequestMatcherCalls[0][1][0];
        $requestMatcherFirst = $this->container->getDefinition($requestMatcherFirstId);

        $this->assertInstanceOf(Definition::class, $requestMatcherFirst);
        if (!class_exists(ChainRequestMatcher::class)) {
            $this->assertEquals('/api/*', $requestMatcherFirst->getArgument(0));
        } else {
            $this->assertEquals('/api/*', $requestMatcherFirst->getArgument(0)[0]->getArgument(0));
        }

        // Second zone
        $this->assertEquals('addRequestMatcher', $addRequestMatcherCalls[1][0]);
        $requestMatcherSecondId = (string) $addRequestMatcherCalls[1][1][0];
        $requestMatcherSecond = $this->container->getDefinition($requestMatcherSecondId);

        $this->assertInstanceOf(Definition::class, $requestMatcherSecond);
        if (!class_exists(ChainRequestMatcher::class)) {
            $this->assertEquals('/^second', $requestMatcherSecond->getArgument(0));
            $this->assertEquals(['127.0.0.1'], $requestMatcherSecond->getArgument(3));
        } else {
            $this->assertEquals('/^second', $requestMatcherSecond->getArgument(0)[0]->getArgument(0));
            $this->assertEquals(['127.0.0.1'], $requestMatcherSecond->getArgument(0)[2]->getArgument(0));
        }
    }

    public function testMimeTypesArePassedArrays(): void
    {
        $config = [
            'fos_rest' => [
                'view' => [
                    'mime_types' => [
                        'json' => ['application/json', 'application/x-json'],
                        'jpg' => 'image/jpeg',
                        'png' => 'image/png',
                    ],
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertSame(
            [
                'json' => ['application/json', 'application/x-json'],
                'jpg' => ['image/jpeg'],
                'png' => ['image/png'],
            ],
            $this->container->getDefinition('fos_rest.mime_type_listener')->getArgument(0)
        );
    }

    public function testSerializerErrorRendererNotRegisteredByDefault(): void
    {
        $config = [
            'fos_rest' => [],
        ];
        $this->extension->load($config, $this->container);

        $this->assertFalse($this->container->hasDefinition('fos_rest.error_renderer.serializer'));
        $this->assertFalse($this->container->hasAlias('error_renderer'));
    }

    public function testRegisterSerializerErrorRenderer(): void
    {
        if (!interface_exists(ErrorRendererInterface::class)) {
            $this->markTestSkipped();
        }

        $config = [
            'fos_rest' => [
                'exception' => [
                    'serializer_error_renderer' => true,
                ],
            ],
        ];
        $this->extension->load($config, $this->container);

        $this->assertTrue($this->container->hasDefinition('fos_rest.error_renderer.serializer'));
        $this->assertTrue($this->container->hasAlias('error_renderer'));
        $this->assertSame('fos_rest.error_renderer.serializer', (string) $this->container->getAlias('error_renderer'));
    }
}

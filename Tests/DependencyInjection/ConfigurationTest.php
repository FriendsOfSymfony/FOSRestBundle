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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

/**
 * Class ConfigurationTest.
 *
 * @author Evgenij Efimov <edefimov.it@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    /**
     * Test object.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Configuration processor.
     *
     * @var Processor
     */
    private $processor;

    /**
     * testExceptionCodesAcceptsIntegers.
     */
    public function testExceptionCodesAcceptsIntegers()
    {
        $expectedConfig = [
            \RuntimeException::class => 500,
        ];

        $config = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'codes' => $expectedConfig,
                        'exception_controller' => 'fos_rest.exception.controller::showAction',
                        'forward' => true,
                    ],
                ],
            ]
        );

        self::assertSame($expectedConfig, $config['exception']['codes']);
    }

    /**
     * testThatResponseConstantsConvertedToCodes.
     */
    public function testThatResponseConstantsConvertedToCodes()
    {
        $expectedResult = [
            NotFoundHttpException::class => Response::HTTP_NOT_FOUND,
            MethodNotAllowedException::class => Response::HTTP_METHOD_NOT_ALLOWED,
        ];
        $config = [
            'exception' => [
                'codes' => [
                    NotFoundHttpException::class => 'HTTP_NOT_FOUND',
                    MethodNotAllowedException::class => 'HTTP_METHOD_NOT_ALLOWED',
                ],
                'exception_controller' => 'fos_rest.exception.controller::showAction',
                'forward' => true,
            ],
        ];

        $config = $this->processor->processConfiguration($this->configuration, [$config]);

        self::assertArrayHasKey('codes', $config['exception']);
        self::assertSame($expectedResult, $config['exception']['codes'], 'Response constants were not converted');
    }

    /**
     * testThatIfExceptionCodeIncorrectExceptionIsThrown.
     *
     * @param mixed $value Test value
     *
     * @dataProvider incorrectExceptionCodeProvider
     */
    public function testThatIfExceptionCodeIncorrectExceptionIsThrown($value)
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf('Invalid HTTP code in fos_rest.exception.codes, see %s for all valid codes.', Response::class));

        $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'codes' => [
                            \RuntimeException::class => $value,
                        ],
                        'exception_controller' => 'fos_rest.exception.controller::showAction',
                        'forward' => true,
                    ],
                ],
            ]
        );
    }

    public function testLoadBadMessagesClassThrowsException()
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'exception_controller' => 'fos_rest.exception.controller::showAction',
                        'forward' => true,
                        'messages' => [
                            'UnknownException' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    public function testLoadBadCodesClassThrowsException()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf('Could not load class "UnknownException" or the class does not extend from "%s"', \Exception::class));

        $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'codes' => [
                            'UnknownException' => 404,
                        ],
                        'exception_controller' => 'fos_rest.exception.controller::showAction',
                        'forward' => true,
                    ],
                ],
            ]
        );
    }

    public function testOverwriteFormatListenerRulesDoesNotMerge()
    {
        $configuration = $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'exception_controller' => 'fos_rest.exception.controller::showAction',
                        'forward' => true,
                    ],
                    'format_listener' => [
                        'rules' => [
                            [
                                'path' => '^/admin',
                                'priorities' => ['html'],
                            ],
                            [
                                'path' => '^/',
                                'priorities' => ['html', 'json'],
                            ],
                        ],
                    ],
                ],
                [
                    'format_listener' => [
                        'rules' => [
                            [
                                'path' => '^/',
                                'priorities' => ['json'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        $expected = [
            [
                'path' => '^/',
                'priorities' => ['json'],
                'host' => null,
                'methods' => null,
                'attributes' => [],
                'stop' => false,
                'prefer_extension' => true,
                'fallback_format' => 'html',
            ],
        ];

        $this->assertEquals($expected, $configuration['format_listener']['rules']);
    }

    /**
     * incorrectExceptionCodeProvider.
     *
     * @return array
     */
    public function incorrectExceptionCodeProvider()
    {
        return [
            ['404'], // Integer as string in not acceptable
            ['Any text'],
            [true],
            [false],
            [null],
        ];
    }

    public function testTemplatingServiceMustBeNull()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('only null is supported');

        $this->processor->processConfiguration($this->configuration, [
            [
                'service' => [
                    'templating' => 'twig',
                ],
            ],
        ]);
    }

    public function testDefaultEngineMustBeNull()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('only null is supported');

        $this->processor->processConfiguration($this->configuration, [
            [
                'view' => [
                    'default_engine' => 'twig',
                ],
            ],
        ]);
    }

    public function testForceRedirectsMustBeEmptyArray()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('only the empty array is supported');

        $this->processor->processConfiguration($this->configuration, [
            [
                'view' => [
                    'force_redirects' => [
                        'html' => true,
                    ],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->configuration = new Configuration(false);
        $this->processor = new Processor();
    }
}

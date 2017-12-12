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
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Invalid HTTP code in fos_rest.exception.codes, see Symfony\Component\HttpFoundation\Response for all valid codes.
     * @dataProvider incorrectExceptionCodeProvider
     */
    public function testThatIfExceptionCodeIncorrectExceptionIsThrown($value)
    {
        $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'codes' => [
                            \RuntimeException::class => $value,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoadBadMessagesClassThrowsException()
    {
        $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'messages' => [
                            'UnknownException' => true,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage Could not load class 'UnknownException' or the class does not extend from '\Exception'
     */
    public function testLoadBadCodesClassThrowsException()
    {
        $this->processor->processConfiguration(
            $this->configuration,
            [
                [
                    'exception' => [
                        'codes' => [
                            'UnknownException' => 404,
                        ],
                    ],
                ],
            ]
        );
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

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
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ConfigurationTest.
 *
 * @author Evgenij Efimov <edefimov.it@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
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
     * testAcceptsIntegers.
     */
    public function testAcceptsIntegers()
    {
        $expectedConfig = [
            md5(microtime()) => mt_rand(200, 599),
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
        $ref = new \ReflectionClass(Response::class);
        $expectedResult = [];
        $config = [];

        foreach ($ref->getConstants() as $constantName => $value) {
            if (strpos($constantName, 'HTTP_') !== 0) {
                continue;
            }

            $fakeExceptionClassName = md5(microtime().$constantName);
            $expectedResult[$fakeExceptionClassName] = $value;

            $config['exception']['codes'][$fakeExceptionClassName] = $constantName;
        }

        $config = $this->processor->processConfiguration($this->configuration, [$config]);

        self::assertTrue(isset($config['exception']['codes']));
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
                            'no-matter' => $value,
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

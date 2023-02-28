<?php

declare(strict_types=1);

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Request;

use FOS\RestBundle\Filter\IdFilter;
use FOS\RestBundle\Pagination\LimitOffsetPagination;
use FOS\RestBundle\Request\QueryStringParamConverter;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Bartek Chmura <bartek@nuvola.pl>
 */
final class QueryStringParamConverterTest extends TestCase
{
    /**
     * @var DenormalizerInterface
     */
    protected $denormalizer;

    /**
     * @var QueryStringParamConverter
     */
    protected $converter;

    /**
     * @dataProvider applyDataProvider
     */
    public function testApply(array $query, string $class, string $name, bool $expected): void
    {
        $apply = $this->converter->apply(
            $this->createRequest($query),
            $this->createConfiguration($class, $name)
        );

        self::assertSame($expected, $apply);
    }

    protected function createRequest(array $query = []): Request
    {
        return new Request(
            $query
        );
    }

    protected function createConfiguration(string $class, string $name): ParamConverter
    {
        return new ParamConverter(
            [
                'name' => $name,
                'class' => $class,
                'options' => [],
                'converter' => 'fos_rest.query_string',
            ]
        );
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $class, string $name, bool $expected): void
    {
        $supports = $this->converter->supports(
            $this->createConfiguration($class, $name)
        );

        self::assertSame($expected, $supports);
    }

    public function supportsDataProvider(): iterable
    {
        return [
            'Is LimitOffset pagination supported?' => [
                'class' => LimitOffsetPagination::class,
                'name' => 'pagination',
                'expected' => true,
            ],
            'Is Page pagination supported?' => [
                'class' => LimitOffsetPagination::class,
                'name' => 'pagination',
                'expected' => true,
            ],
            'Is IdFilter pagination supported?' => [
                'class' => IdFilter::class,
                'name' => 'filter',
                'expected' => true,
            ],
        ];
    }

    public function applyDataProvider(): iterable
    {
        return [
            'No pagination' => [
                'query' => [],
                'class' => LimitOffsetPagination::class,
                'name' => 'pagination',
                'expected' => false,
            ],
            'LimitOffset pagination' => [
                'query' => [
                    'pagination' => [
                        'limit' => '16',
                        'offset' => '0',
                    ],
                ],
                'class' => LimitOffsetPagination::class,
                'name' => 'pagination',
                'expected' => true,
            ],
            'Page pagination' => [
                'query' => [
                    'pagination' => [
                        'page' => '1',
                    ],
                ],
                'class' => LimitOffsetPagination::class,
                'name' => 'pagination',
                'expected' => true,
            ],
            'IdFilter collection' => [
                'query' => [
                    'filter' => [
                        'ids' => [
                            '307e3371-e878-4dd6-883f-5596d5f01e93',
                        ],
                    ],
                ],
                'class' => LimitOffsetPagination::class,
                'name' => 'filter',
                'expected' => true,
            ],
            'IdFilter' => [
                'query' => [
                    'filter' => [
                        'id' => '307e3371-e878-4dd6-883f-5596d5f01e93',
                    ],
                ],
                'class' => LimitOffsetPagination::class,
                'name' => 'filter',
                'expected' => true,
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->converter = new QueryStringParamConverter($this->denormalizer);
    }
}

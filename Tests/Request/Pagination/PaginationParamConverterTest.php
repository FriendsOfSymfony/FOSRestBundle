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

namespace FOS\RestBundle\Tests\Request\Pagination;

use FOS\RestBundle\Request\Pagination\LimitOffsetPagination;
use FOS\RestBundle\Request\Pagination\PaginationParamConverter;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * @author Bartek Chmura <bartek@nuvola.pl>
 */
class PaginationParamConverterTest extends TestCase
{
    /**
     * @var DenormalizerInterface
     */
    protected $denormalizer;

    /**
     * @var PaginationParamConverter
     */
    protected $converter;

    /**
     * @dataProvider applyDataProvider
     */
    public function testApply(array $query, string $class, bool $expected): void
    {
        $apply = $this->converter->apply(
            $this->createRequest($query),
            $this->createConfiguration($class, 'pagination')
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
                'name'      => $name,
                'class'     => $class,
                'options'   => [],
                'converter' => 'fos_rest.pagination',
            ]
        );
    }

    /**
     * @dataProvider supportsDataProvider
     */
    public function testSupports(string $class, bool $expected): void
    {
        $supports = $this->converter->supports(
            $this->createConfiguration($class, 'pagination')
        );

        self::assertSame($expected, $supports);
    }

    public function supportsDataProvider(): iterable
    {
        return [
            'Is LimitOffset pagination supported?' => [
                'class'    => LimitOffsetPagination::class,
                'expected' => true,
            ],
            'Is Page pagination supported?'        => [
                'class'    => LimitOffsetPagination::class,
                'expected' => true,
            ],
        ];
    }

    public function applyDataProvider(): iterable
    {
        return [
            'No pagination'          => [
                'query'    => [],
                'class'    => LimitOffsetPagination::class,
                'expected' => false,
            ],
            'LimitOffset pagination' => [
                'query'    => [
                    'pagination' => [
                        'limit'  => '16',
                        'offset' => '0',
                    ],
                ],
                'class'    => LimitOffsetPagination::class,
                'expected' => true,
            ],
            'Page pagination'        => [
                'query'    => [
                    'pagination' => [
                        'page' => '1',
                    ],
                ],
                'class'    => LimitOffsetPagination::class,
                'expected' => true,
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->denormalizer = $this->createMock(DenormalizerInterface::class);
        $this->converter    = new PaginationParamConverter($this->denormalizer);
    }
}

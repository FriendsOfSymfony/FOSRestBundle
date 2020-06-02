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

namespace FOS\RestBundle\Pagination;

/**
 * @author Bartek Chmura <bartek@nuvola.pl>
 */
final class LimitOffsetPagination implements PaginationInterface
{
    const DEFAULT_LIMIT = "16";

    const DEFAULT_OFFSET = "0";

    /**
     * @var string
     */
    private $limit = self::DEFAULT_LIMIT;

    /**
     * @var string
     */
    private $offset = self::DEFAULT_OFFSET;

    public function getLimit(): string
    {
        return $this->limit;
    }

    public function setLimit(string $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function getOffset(): string
    {
        return $this->offset;
    }

    public function setOffset(string $offset): self
    {
        $this->offset = $offset;

        return $this;
    }
}

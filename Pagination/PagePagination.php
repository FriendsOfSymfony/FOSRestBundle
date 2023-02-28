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
final class PagePagination implements PaginationInterface
{
    const DEFAULT_PAGE = '1';

    /**
     * @var string
     */
    private $page = self::DEFAULT_PAGE;

    public function getPage(): string
    {
        return $this->page;
    }

    public function setPage(string $page): self
    {
        $this->page = $page;

        return $this;
    }
}

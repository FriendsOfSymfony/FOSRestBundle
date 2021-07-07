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

namespace FOS\RestBundle\Filter;

/**
 * @author Bartek Chmura <bartek@nuvola.pl>
 */
final class IdFilter implements FilterInterface
{
    /**
     * @var string[]
     */
    private $ids;

    /**
     * @var string
     */
    private $id;

    /**
     * @return string[]
     */
    public function getIds(): iterable
    {
        return $this->ids;
    }

    public function isIdsSet(): bool
    {
        return isset($this->ids);
    }

    public function setIds(array $ids): self
    {
        $this->ids = $ids;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isIdSet(): bool
    {
        return isset($this->id);
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }
}

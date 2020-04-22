<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

/**
 * Provides the available view formats.
 *
 * @author Alexander Schranz <alexander@sulu.io>
 */
class FormatsProvider implements FormatsProviderInterface
{
    /**
     * @var array
     */
    private $formats;

    /**
     * @param array $formats
     */
    public function __construct(array $formats = [])
    {
        $this->formats = $formats;
    }

    public function getFormats(): array
    {
        return $this->formats;
    }
}

<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\View;

use FOS\RestBundle\View\FormatsProvider;
use PHPUnit\Framework\TestCase;

/**
 * FormatsProvider test.
 *
 * @author Alexander Schranz <alexander@sulu.io>
 */
class FormatsProviderTest extends TestCase
{
    public function testGetFormats()
    {
        $formatsProvider = new FormatsProvider([
            'xml' => true,
            'json' => true,
        ]);

        $this->assertEquals(
            [
                'xml' => true,
                'json' => true,
            ],
            $formatsProvider->getFormats()
        );
    }
}

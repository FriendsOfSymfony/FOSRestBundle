<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Fixture for testing whether the ParamFetcher can be injected into
 * a type-hinted controller method.
 */
class ParamFetcherUnionTypeController extends ParamFetcherController
{
    /**
     * Make sure the ParamFetcher can be injected according to the mixed typehint.
     */
    public function byUnionTypeAction(ParamFetcher|ParamFetcherInterface $pfu)
    {
    }
}

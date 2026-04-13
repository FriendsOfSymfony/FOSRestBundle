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

use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Fixture for testing whether the ParamFetcher can be injected into
 * a disjunctive normal form type-hinted controller method.
 */
class ParamFetcherDnfTypeController extends ParamFetcherIntersectionTypeController
{
    /**
     * Make sure the ParamFetcher can be injected according to the DNF typehint.
     */
    public function byDnfTypeAction((ParamFetcherInterface&ParamFetcherIntersectionTypeMarkerInterface)|null $pfd)
    {
    }
}

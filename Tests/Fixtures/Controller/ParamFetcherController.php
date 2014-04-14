<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use FOS\RestBundle\Request\ParamFetcher;

/**
 * Fixture for testing whether the ParamFetcher can be injected into
 * a type-hinted controller method.
 */
class ParamFetcherController
{
    /**
     * Make sure the ParamFetcher can be injected by name.
     */
    public function byNameAction($paramFetcher)
    {}

    /**
     * Make sure the ParamFetcher can be injected according to the typehint.
     */
    public function byTypeAction(ParamFetcher $pf)
    {}

    /**
     * Make sure the ParamFetcher can be set as a request attribute even if
     * there is no controller parameter to receive it.
     */
    public function notProvidedAction()
    {}
}

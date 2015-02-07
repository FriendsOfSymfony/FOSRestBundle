<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamFetcherInterface;

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
     * Make sure the ParamFetcher can be injected if the typehint is for
     * the interface.
     */
    public function byInterfaceAction(ParamFetcherInterface $pfi)
    {}

    /**
     * Make sure the ParamFetcher can be set as a request attribute even if
     * there is no controller parameter to receive it.
     */
    public function notProvidedAction()
    {}

    /**
     * Make sure the ParamFetcher can be set for controller which are used as invokable
     */
    public function __invoke(ParamFetcher $pfInvokable)
    {}
}

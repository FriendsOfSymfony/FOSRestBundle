<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\EventListener;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\EventListener\ParamFetcherListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamReaderInterface;
use FOS\RestBundle\Tests\Fixtures\Controller\ParamFetcherController;
use FOS\RestBundle\Tests\Fixtures\Controller\ParamFetcherUnionTypeController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Param Fetcher Listener Tests.
 */
class ParamFetcherListenerTest extends TestCase
{
    private $requestStack;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paramFetcher;

    /**
     * @var ParamFetcherListener
     */
    private $paramFetcherListener;

    /**
     * Tests the ParamFetcher being able to set an attribute on the request
     * when configured to do so and the attribute is specified as a null
     * default value.
     */
    public function testSettingAttributes()
    {
        $request = new Request(['customer' => '5']);
        $request->attributes->set('customer', null);
        $event = $this->getEvent($request);

        $this->paramFetcherListener->onKernelController($event);

        $this->assertEquals(5, $request->attributes->get('customer'), 'Listener set attribute as expected');
    }

    /**
     * Tests the ParamFetcher being able to set an attribute on the request
     * when configured to do so and the attribute is specified as a null
     * default value.
     */
    public function testSettingParamFetcherOnRequest()
    {
        $request = new Request();
        $event = $this->getEvent($request);

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('paramFetcher'));
    }

    public function testParamFetcherOnRequestNoZone()
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
        $event = $this->getEvent($request);

        $this->paramFetcherListener->onKernelController($event);

        $this->assertNull($request->attributes->get('paramFetcher'));
    }

    /**
     * Tests that the ParamFetcher can be injected by the default name
     * ($paramFetcher) or by a different name if type-hinted.
     *
     * @dataProvider setParamFetcherByTypehintProvider
     */
    public function testSettingParamFetcherByTypehint($actionName, $expectedAttribute)
    {
        $request = new Request();

        $event = $this->getEvent($request, $actionName);

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get($expectedAttribute));
    }

    /**
     * Tests that the ParamFetcher can be injected in a invokable controller.
     */
    public function testSettingParamFetcherForInvokable()
    {
        $request = new Request();
        $event = $this->getEvent($request, null);

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('pfInvokable'));
    }

    public function setParamFetcherByTypehintProvider()
    {
        $paramFetcher = [
            // Without a typehint, the ParamFetcher should be injected as
            // $paramFetcher.
            ['byNameAction', 'paramFetcher'],

            // With a typehint, the ParamFetcher should be injected as whatever
            // the parameter name is.
            ['byTypeAction', 'pf'],

            // The user can typehint using ParamFetcherInterface, too.
            ['byInterfaceAction', 'pfi'],

            // If there is no controller argument for the ParamFetcher, it
            // should be injected as the default name.
            ['notProvidedAction', 'paramFetcher'],
        ];

        if (\PHP_VERSION_ID >= 80000) {
            // With a mixed typehint, the ParamFetcher should be injected as whatever
            // the parameter name is.
            $paramFetcher[] = ['byUnionTypeAction', 'pfu'];
        }

        return $paramFetcher;
    }

    protected function getEvent(Request $request, $actionMethod = 'byNameAction')
    {
        $this->requestStack->push($request);

        $controller = \PHP_VERSION_ID < 80000 ? new ParamFetcherController() : new ParamFetcherUnionTypeController();
        $callable = $actionMethod ? [$controller, $actionMethod] : $controller;
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ControllerEvent($kernel, $callable, $request, null);
    }

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();

        $queryParam = new QueryParam();
        $queryParam->key = 'customer';

        $paramReader = $this->createMock(ParamReaderInterface::class);
        $paramReader
            ->method('read')
            ->willReturn([
                'customer' => $queryParam,
            ]);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->paramFetcher = new ParamFetcher(
            $this->createMock(ContainerInterface::class),
            $paramReader,
            $this->requestStack,
            $validator
        );
        $this->paramFetcherListener = new ParamFetcherListener($this->paramFetcher, true);
    }
}

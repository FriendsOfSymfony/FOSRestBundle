<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Controller;

use FOS\RestBundle\Controller\ExceptionController;
use FOS\RestBundle\Util\ExceptionValueMap;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionControllerTest extends TestCase
{
    /**
     * @dataProvider provideThrowables
     */
    public function testShowActionWithError(\Throwable $throwable): void
    {
        $request = Request::create('/');
        $request->headers->set('X-Php-Ob-Level', '2');

        $response = new Response();

        $viewHandler = $this->createMock(ViewHandlerInterface::class);
        $viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(View::class))
            ->willReturn($response);

        $controller = new ExceptionController($viewHandler, new ExceptionValueMap([]), true);

        $this->assertSame($response, $controller->showAction($request, $throwable));
    }

    public function provideThrowables(): array
    {
        return [
            [new \Error()],
            [new \Exception()],
        ];
    }
}

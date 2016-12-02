<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller;

use FOS\RestBundle\Controller\Annotations\FileParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\Constraints\IdenticalTo;

class ParamFetcherController extends FOSRestController
{
    /**
     * @RequestParam(name="raw", requirements=@IdenticalTo({"foo"="raw", "bar"="foo"}), default="invalid", strict=false)
     * @RequestParam(name="map", map=true, requirements=@IdenticalTo({"foo"="map", "foobar"="foo"}), default="%invalid2% %%", strict=false)
     * @RequestParam(name="bar", nullable=true, requirements="%bar%\ foo")
     * @QueryParam(name="foz", requirements="[a-z]+")
     * @QueryParam(name="baz", requirements="[a-z]+", incompatibles={"foz"})
     */
    public function paramsAction(ParamFetcherInterface $fetcher)
    {
        return new JsonResponse($fetcher->all());
    }

    /**
     * @QueryParam(name="foo", default="invalid")
     * @RequestParam(name="bar", default="%foo%")
     */
    public function testAction(Request $request, ParamFetcherInterface $fetcher)
    {
        $paramsBefore = $fetcher->all();

        $newRequest = new Request();
        $newRequest->query = $request->query;
        $newRequest->request = $request->request;
        $newRequest->attributes->set('_controller', sprintf('%s::paramsAction', __CLASS__));
        $response = $this->container->get('http_kernel')->handle($newRequest, HttpKernelInterface::SUB_REQUEST, false);

        $paramsAfter = $fetcher->all(false);

        return new JsonResponse(array(
            'before' => $paramsBefore,
            'during' => json_decode($response->getContent(), true),
            'after' => $paramsAfter,
        ));
    }

    /**
     * @FileParam(name="single_file", strict=false, default="noFile")
     */
    public function singleFileAction(ParamFetcherInterface $fetcher)
    {
        /** @var UploadedFile $file */
        $file = $fetcher->get('single_file');

        return new JsonResponse(array(
            'single_file' => $this->printFile($file),
        ));
    }

    /**
     * @FileParam(name="array_files", map=true)
     */
    public function fileCollectionAction(ParamFetcherInterface $fetcher)
    {
        $files = $fetcher->get('array_files');

        return new JsonResponse(array(
            'array_files' => [
                $this->printFile($files[0]),
                $this->printFile($files[1]),
            ],
        ));
    }

    /**
     * @FileParam(name="array_images", image=true, strict=false, map=true, default="NotAnImage")
     */
    public function imageCollectionAction(ParamFetcherInterface $fetcher)
    {
        $files = $fetcher->get('array_images');

        return new JsonResponse(array(
            'array_images' => (is_string($files)) // Default message on validation error
                ? $files
                : [
                    $this->printFile($files[0]),
                    $this->printFile($files[1]),
                ],
        ));
    }

    private function printFile($file)
    {
        return ($file instanceof UploadedFile) ? $file->getClientOriginalName() : $file;
    }
}

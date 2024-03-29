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
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Extract from the documentation.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class ParamsAnnotatedController
{
    /**
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     * @RequestParam(name="byauthor", requirements="[a-z]+", description="by author", incompatibles={"search"}, strict=true)
     * @QueryParam(name="filters", map=true, requirements=@NotNull)
     * @FileParam(name="avatar", requirements={"mimeTypes"="application/json"}, image=true)
     * @FileParam(name="foo", requirements=@NotNull, strict=false)
     * @FileParam(name="bar", requirements=@NotNull, map=true)
     */
    public function getArticlesAction(ParamFetcher $paramFetcher)
    {
    }

    #[QueryParam(name: 'page', requirements: '\d+', default: '1', description: 'Page of the overview')]
    #[RequestParam(name: 'byauthor', requirements: '[a-z]+', description: 'by author', incompatibles: ['search'], strict: true)]
    #[QueryParam(name: 'filters', requirements: '\d+', default: '1', description: 'Page of the overview')]
    #[FileParam(name: 'avatar', requirements: ['mimeTypes' => 'application/json'], image: true)]
    #[FileParam(name: 'foo', strict: false)]
    #[FileParam(name: 'bar', map: true)]
    public function getArticlesAttributesAction(ParamFetcher $paramFetcher)
    {
    }
}

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

use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Bartłomiej Nowak <barteknowak90@gmail.com>
 * @Rest\RouteResource("Comment", parents={"Article"})
 */
class AnnotatedParentsArticleController extends Controller
{
    /**
     * "get_article_comment" [GET] /articles/{slug}/comments/{id}.{_format}
     */
    public function getAction($slug, $id)
    {
    }

    /**
     * "get_article_comments" [GET] /articles/{slug}/comments.{_format}
     */
    public function cgetAction($slug)
    {
    }
}

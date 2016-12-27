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

use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class VersionController extends FOSRestController
{
    /**
     * @View("TestBundle:Version:version.html.twig")
     */
    public function versionAction(Request $request, $version)
    {
        $versionExclusion = $this->findExclusionStrategyVersion($request);

        return array(
            'version' => $version,
            'version_exclusion' => $versionExclusion,
        );
    }

    private function findExclusionStrategyVersion(Request $request)
    {
        $view = $this->view([]);
        $response = $this->get('fos_rest.view_handler')->createResponse($view, $request, 'json');

        return $view->getContext()->getVersion();
    }
}

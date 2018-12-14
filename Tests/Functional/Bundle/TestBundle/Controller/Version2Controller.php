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

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Version;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @Version({"1.2"})
 */
class Version2Controller extends AbstractFOSRestController
{
    /**
     * @View("TestBundle:Version:version.html.twig")
     * @Get(path="/version")
     */
    public function versionAction($version)
    {
        return array('version' => 'test annotation');
    }

    /**
     * @View("TestBundle:Version:version.html.twig")
     * @Get(path="/version/{version}")
     */
    public function versionPathAction(Request $request, $version)
    {
        $versionExclusion = $this->findExclusionStrategyVersion($request);

        return array(
            'version' => 'test annotation',
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

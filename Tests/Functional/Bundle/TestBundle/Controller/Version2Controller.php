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
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class Version2Controller extends AbstractFOSRestController
{
    /**
     * @View()
     *
     * @Get(path="/version", condition="request.attributes.get('version') in ['1.2']")
     */
    #[View]
    #[Get(path: '/version', condition: 'request.attributes.get("version") in ["1.2"]')]
    public function versionAction($version)
    {
        return ['version' => 'test annotation'];
    }

    /**
     * @View()
     *
     * @Get(path="/version/{version}", requirements={"version": "1.2"})
     */
    #[View]
    #[Get(path: '/version/{version}', requirements: ['version' => '1.2'])]
    public function versionPathAction(Request $request, $version)
    {
        $versionExclusion = $this->findExclusionStrategyVersion($request);

        return [
            'version' => 'test annotation',
            'version_exclusion' => $versionExclusion,
        ];
    }

    private function findExclusionStrategyVersion(Request $request)
    {
        $view = $this->view([]);
        $response = $this->getViewHandler()->createResponse($view, $request, 'json');

        return $view->getContext()->getVersion();
    }
}

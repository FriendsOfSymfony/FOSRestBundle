<?php

namespace FOS\RestBundle\Tests\Routing\Loader;

use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\Yaml\Yaml;

use FOS\RestBundle\Routing\Loader\RestRouteLoader;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * Base Loader testing class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class LoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Load routes etalon from yml fixture file under Tests\Fixtures directory.
     *
     * @param   string  $etalonName     name of the YML fixture
     */
    protected function loadEtalonRoutesInfo($etalonName)
    {
        return Yaml::load(__DIR__ . '/../../Fixtures/Etalon/' . $etalonName);
    }

    private function getAnnotationReader()
    {
        $reader = new AnnotationReader();
        $reader->setAnnotationNamespaceAlias('FOS\RestBundle\Controller\Annotations\\', 'rest');
        return $reader;
    }

    protected function getControllerLoader()
    {
        $c = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->
            disableOriginalConstructor()->
            getMock();
        $p = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')->
            disableOriginalConstructor()->
            getMock();

        return new RestRouteLoader($c, $p, $this->getAnnotationReader());
    }
}

<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Routing\Loader;

use Doctrine\Common\Annotations\AnnotationReader;

use Symfony\Component\Yaml\Yaml;

use FOS\RestBundle\Routing\Loader\RestRouteLoader;
use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;
use FOS\RestBundle\Routing\Loader\Reader\RestActionReader;
use FOS\RestBundle\Request\ParamReader;

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
        return Yaml::parse(__DIR__ . '/../../Fixtures/Etalon/' . $etalonName);
    }

    private function getAnnotationReader()
    {
        return new AnnotationReader();
    }

    protected function getControllerLoader()
    {
        $c = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $p = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')
            ->disableOriginalConstructor()
            ->getMock();

        $annotationReader = $this->getAnnotationReader();
        $paramReader = new ParamReader($annotationReader);

        $ar = new RestActionReader($annotationReader, $paramReader);
        $cr = new RestControllerReader($ar, $annotationReader);

        return new RestRouteLoader($c, $p, $cr, 'html');
    }
}

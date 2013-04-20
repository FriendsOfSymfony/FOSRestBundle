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
use Symfony\Component\Config\FileLocator;

use FOS\RestBundle\Routing\Loader\RestRouteLoader;
use FOS\RestBundle\Routing\Loader\RestRouteDirectoryLoader;
use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;
use FOS\RestBundle\Routing\Loader\Reader\RestActionReader;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Util\Inflector\DoctrineInflector;

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
     * @param string $etalonName name of the YML fixture
     */
    protected function loadEtalonRoutesInfo($etalonName)
    {
        return Yaml::parse(__DIR__ . '/../../Fixtures/Etalon/' . $etalonName);
    }

    private function getAnnotationReader()
    {
        return new AnnotationReader();
    }

    private function getControllerLoaderArguments()
    {
        $c = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $l = new FileLocator();
        $p = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')
            ->disableOriginalConstructor()
            ->getMock();

        $annotationReader = $this->getAnnotationReader();
        $paramReader = new ParamReader($annotationReader);
        $inflector = new DoctrineInflector();

        $ar = new RestActionReader($annotationReader, $paramReader, $inflector, true);
        $cr = new RestControllerReader($ar, $annotationReader);

        return array($c, $l, $p, $cr, 'html');
    }

    protected function getControllerLoader()
    {
        list($c, $l, $p, $cr, $df) = $this->getControllerLoaderArguments();

        return new RestRouteLoader($c, $l, $p, $cr, $df);
    }

    protected function getControllerDirectoryLoader()
    {
        list($c, $l, $p, $cr, $df) = $this->getControllerLoaderArguments();

        return new RestRouteDirectoryLoader($c, $l, $p, $cr, $df);
    }
}

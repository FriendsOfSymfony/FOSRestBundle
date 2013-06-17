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

    /**
     * Buils a RestRouteLoader
     *
     * @param array $formats available resource formats
     */
    protected function getControllerLoader(array $formats = array())
    {
        $c = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $l = $this->getMockBuilder('Symfony\Component\Config\FileLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $p = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')
            ->disableOriginalConstructor()
            ->getMock();

        $annotationReader = $this->getAnnotationReader();
        $paramReader = new ParamReader($annotationReader);
        $inflector = new DoctrineInflector();

        $ar = new RestActionReader($annotationReader, $paramReader, $inflector, true, $formats);
        $cr = new RestControllerReader($ar, $annotationReader);

        return new RestRouteLoader($c, $l, $p, $cr, 'html');
    }
}

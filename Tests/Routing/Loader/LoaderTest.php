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
use FOS\RestBundle\Inflector\DoctrineInflector;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Routing\Loader\Reader\RestActionReader;
use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;
use FOS\RestBundle\Routing\Loader\RestRouteLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

/**
 * Base Loader testing class.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
abstract class LoaderTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * Load routes etalon from yml fixture file under Tests\Fixtures directory.
     *
     * @param string $etalonName name of the YML fixture
     *
     * @return array
     */
    protected function loadEtalonRoutesInfo($etalonName)
    {
        return Yaml::parse(file_get_contents(__DIR__.'/../../Fixtures/Etalon/'.$etalonName));
    }

    private function getAnnotationReader()
    {
        return new AnnotationReader();
    }

    /**
     * Builds a RestRouteLoader.
     *
     * @param array $formats         available resource formats
     * @param bool  $hasMethodPrefix
     *
     * @return RestRouteLoader
     */
    protected function getControllerLoader(array $formats = [], $hasMethodPrefix = true)
    {
        // This check allows to override the container
        if (null === $this->container) {
            $this->container = $this->getMockBuilder(ContainerBuilder::class)
                ->disableOriginalConstructor()
                ->setMethods(['get', 'has'])
                ->getMock();
        }
        $l = $this->getMockBuilder('Symfony\Component\Config\FileLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $p = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser')
            ->disableOriginalConstructor()
            ->getMock();

        $annotationReader = $this->getAnnotationReader();
        $paramReader = new ParamReader($annotationReader);
        $inflector = new DoctrineInflector();

        $ar = new RestActionReader($annotationReader, $paramReader, $inflector, true, $formats, $hasMethodPrefix);
        $cr = new RestControllerReader($ar, $annotationReader);

        return new RestRouteLoader($this->container, $l, $p, $cr, 'html');
    }
}

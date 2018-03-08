<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional;

use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Serializer\JMSHandlerRegistry;
use FOS\RestBundle\Serializer\Normalizer\FormErrorHandler;
use JMS\SerializerBundle\JMSSerializerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class DependencyInjectionTest extends KernelTestCase
{
    public function testSerializerRelatedServicesAreNotRemovedWhenJmsSerializerBundleIsEnabled()
    {
        self::bootKernel();
        $container = self::$kernel->getContainer();

        $this->assertInstanceOf(FormErrorHandler::class, $container->get('jms_serializer.form_error_handler'));
        $this->assertInstanceOf(JMSHandlerRegistry::class, $container->get('test.jms_serializer.handler_registry'));
    }

    protected static function getKernelClass()
    {
        return TestKernel::class;
    }
}

class TestKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new FOSRestBundle(),
            new JMSSerializerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'secret' => 'test',
                'router' => array(
                    'resource' => '%kernel.root_dir%/config/routing.yml',
                ),
            ]);
            $container->loadFromExtension('fos_rest', [
                'exception' => null,
            ]);
            $container->setAlias('test.jms_serializer.handler_registry', new Alias('jms_serializer.handler_registry', true));
        });
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/'.str_replace('\\', '-', get_class($this)).'/cache/'.$this->environment;
    }
}

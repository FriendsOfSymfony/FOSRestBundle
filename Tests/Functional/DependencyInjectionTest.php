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
use FOS\RestBundle\Serializer\JMSHandlerRegistryV2;
use FOS\RestBundle\Serializer\Normalizer\FormErrorHandler;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use JMS\SerializerBundle\Debug\TraceableHandlerRegistry;
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

        $this->assertInstanceOf(FormErrorHandler::class, $container->get('test.jms_serializer.form_error_handler'));

        if (class_exists(TraceableHandlerRegistry::class)) {
            $this->markTestIncomplete('Starting from jms/serializer-bundle 4.0 the handler registry is not decorated anymore');
        }

        $this->assertInstanceOf(
            interface_exists(SerializationVisitorInterface::class) ? JMSHandlerRegistryV2::class : JMSHandlerRegistry::class,
            $container->get('test.jms_serializer.handler_registry')
        );
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}

class TestKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new FOSRestBundle(),
            new JMSSerializerBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            $frameworkConfig = [
                'annotations' => [
                    'enabled' => true,
                ],
                'secret' => 'test',
                'router' => [
                    'resource' => '%kernel.project_dir%/config/routing.yml',
                    'utf8' => true,
                ],
            ];

            if (Kernel::VERSION_ID >= 70000) {
                unset($frameworkConfig['annotations']);
            }

            $container->loadFromExtension('framework', $frameworkConfig);
            $container->loadFromExtension('fos_rest', []);
            $container->setAlias('test.jms_serializer.handler_registry', new Alias('jms_serializer.handler_registry', true));
            $container->setAlias('test.jms_serializer.form_error_handler', new Alias('jms_serializer.form_error_handler', true));
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/'.str_replace('\\', '-', get_class($this)).'/cache/'.$this->environment;
    }
}

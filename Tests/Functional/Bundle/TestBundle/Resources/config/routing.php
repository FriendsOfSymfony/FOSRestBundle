<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

$routes->add('request_body_param_converter', new Route('/body-converter', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\RequestBodyParamConverterController::putPostAction' : 'TestBundle:RequestBodyParamConverter:putPost',
    'date' => '16-06-2016',
)));

$routes->add('test_serializer_error_exception', new Route('/serializer-error/exception.{_format}', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\SerializerErrorController::logicExceptionAction' : 'TestBundle:SerializerError:logicException',
)));

$routes->add('test_serializer_unknown_exception', new Route('/serializer-error/unknown_exception.{_format}', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\SerializerErrorController::unknownExceptionAction' : 'TestBundle:SerializerError:unknownException',
)));

$routes->add('test_serializer_error_invalid_form', new Route('/serializer-error/invalid-form.{_format}', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\SerializerErrorController::invalidFormAction' : 'TestBundle:SerializerError:invalidForm',
)));

// Must be defined before test_version
$routes->addCollection($loader->import('FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\Version2Controller', 'rest'));

$routes->add('test_version', new Route('/version', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\VersionController::versionAction' : 'TestBundle:Version:version',
), array(
    'version' => '2.1|3.4.2|2.3',
)));

$routes->add('test_version_path', new Route('/version/{version}', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\VersionController::versionAction' : 'TestBundle:Version:version',
)));

$routes->add('test_param_fetcher', new Route('/params', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ParamFetcherController::paramsAction' : 'TestBundle:ParamFetcher:params',
)));

$routes->add('test_param_fetcher_test', new Route('/params/test', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ParamFetcherController::testAction' : 'TestBundle:ParamFetcher:test',
)));

$routes->add('test_param_fetcher_file_test', new Route('/file/test', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ParamFetcherController::singleFileAction' : 'TestBundle:ParamFetcher:singleFile',
)));

$routes->add('test_param_fetcher_file_collection_test', new Route('/file/collection/test', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ParamFetcherController::fileCollectionAction' : 'TestBundle:ParamFetcher:fileCollection',
)));

$routes->add('test_param_fetcher_image_collection_test', new Route('/image/collection/test', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ParamFetcherController::imageCollectionAction' : 'TestBundle:ParamFetcher:imageCollection',
)));

$routes->addCollection($loader->import('FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ArticleController', 'rest'));

$routes->add('test_redirect_endpoint', new Route('/hello/{name}', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\ArticleController::redirectAction' : 'TestBundle:Article:redirect',
)));

$routes->add('test_allowed_methods1', new Route('/allowed-methods', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\AllowedMethodsController::indexAction' : 'TestBundle:AllowedMethods:index',
), array(), array(), '', array(), array('GET', 'LOCK')));

$routes->add('test_allowed_methods2', new Route('/allowed-methods', array(
    '_controller' => Kernel::VERSION_ID >= 40100 ? 'FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\AllowedMethodsController::indexAction' : 'TestBundle:AllowedMethods:index',
), array(), array(), '', array(), array('POST', 'PUT')));

return $routes;

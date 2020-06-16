Upgrading From 2.x To 3.0
=========================

 * Enabling the `fos_rest.routing_loader` option is not supported anymore. Setting
   it to another value than `false` leads to an exception:

   ```yaml
   fos_rest:
       routing_loader: false
   ```

   You need to configure your routes explicitly, e.g. using the Symfony Core annotations or the FOSRestBundle
   shortcuts like `FOS\RestBundle\Controller\Annotations\Get`. You can use
   `bin/console debug:router --show-controllers` to help with the migration and compare routes before and after it.
   Change the routing loading:

   Before:
   ```
   Acme\Controller\TestController:
       type: rest
       resource: Acme\Controller\TestController
   ```

   After:
   ```
   Acme\Controller\TestController:
       type: annotation
       resource: Acme\Controller\TestController
   ```

   When using the Symfony Core route loading, route names might change as the FOSRestBundle used a different naming
   convention. Mind the `.{_format}` suffix if you used the `fos_rest.routing_loader.include_format` option.

   In case you have OpenAPI/Swagger annotations, you can also use [OpenAPI-Symfony-Routing](https://github.com/Tobion/OpenAPI-Symfony-Routing)
   which removes the need to have routing information duplicated. It also allows to add the `.{_format}` suffix automatically as before.

   If migration to explicit routes is not possible or feasible, consider using
   [RestRoutingBundle](https://github.com/handcraftedinthealps/RestRoutingBundle) which extracted the auto-generation of routes
   in a BC way.

 * Support for serializing exceptions has been removed. Setting the
 `fos_rest.exception.serialize_exceptions` option to anything else than `false` leads to an exception.

 * Support for returning anything other than `string` or `null` from `resolve()` when implementing
   the `VersionResolverInterface` has been removed.

 * `Context::setVersion()` does not accept integers anymore.

 * The `isFormatTemplating()`, `renderTemplate()`, and `prepareTemplateParameters()` methods of the
   `ViewHandler` class and the `ViewHandlerInterface` have been removed.

 * The constructor of the `ViewHandler` class has been changed to `private`. Use the static `create()`
   factory method instead.

 * The `setTemplateVar()`, `setPopulateDefaultVars()`, `getTemplateVar()`, and `isPopulateDefaultVars()`
   methods of the `Controller\Annotations\View` class have been removed.

 * The `setEngine()`, `setTemplate()`, `setTemplateData()`, `setTemplateVar()`, `getEngine()`,
   `getTemplate()`, `getTemplateData()`, and `getTemplateVar()` methods of the `View\View` class
   have been removed.

 * The default value of the `fos_rest.body_listener` option has been changed from enabled to disabled.

 * The `setMaxDepth()` method has been removed from the `Context` class. Use the
   `enableMaxDepth()` and `disableMaxDepth()` methods instead.

 * The `getMaxDepth()` method has been removed from the `Context` class. Use the
   `isMaxDepthEnabled()` method instead.

 * The following options have been removed:

   * `fos_rest.access_denied_listener`
   * `fos_rest.exception.exception_controller`
   * `fos_rest.exception.exception_listener`
   * `fos_rest.exception.service`
   * `fos_rest.service.inflector`
   * `fos_rest.service.router`
   * `fos_rest.service.templating`
   * `fos_rest.view.default_engine`
   * `fos_rest.view.force_redirects`
   * `fos_rest.view.templating_formats`

 * The following classes and interfaces have been removed:

   * `FOS\RestBundle\Controller\Annotations\NamePrefix`
   * `FOS\RestBundle\Controller\Annotations\NoRoute`
   * `FOS\RestBundle\Controller\Annotations\Prefix`
   * `FOS\RestBundle\Controller\Annotations\RouteResource`
   * `FOS\RestBundle\Controller\Annotations\Version`
   * `FOS\RestBundle\Controller\ExceptionController`
   * `FOS\RestBundle\Controller\TemplatingExceptionController`
   * `FOS\RestBundle\Controller\TwigExceptionController`
   * `FOS\RestBundle\EventListener\AccessDeniedListener`
   * `FOS\RestBundle\EventListener\ExceptionListener`
   * `FOS\RestBundle\Inflector\DoctrineInflector`
   * `FOS\RestBundle\Inflector\InflectorInterface`
   * `FOS\RestBundle\Routing\Loader\DirectoryRouteLoader`
   * `FOS\RestBundle\Routing\Loader\Reader\RestActionReader`
   * `FOS\RestBundle\Routing\Loader\Reader\RestControllerReader`
   * `FOS\RestBundle\Routing\Loader\RestRouteLoader`
   * `FOS\RestBundle\Routing\Loader\RestRouteProcessor`
   * `FOS\RestBundle\Routing\Loader\RestXmlCollectionLoader`
   * `FOS\RestBundle\Routing\Loader\RestYamlCollectionLoader`
   * `FOS\RestBundle\Routing\ClassResourceInterface`
   * `FOS\RestBundle\Routing\RestRouteCollection`
   * `FOS\RestBundle\Serializer\Normalizer\ExceptionHandler`
   * `FOS\RestBundle\Serializer\Normalizer\ExceptionNormalizer`

 * The following services and aliases have been removed:

   * `fos_rest.access_denied_listener`
   * `fos_rest.exception_listener`
   * `fos_rest.exception.controller`
   * `fos_rest.exception.twig_controller`
   * `fos_rest.routing.loader.controller`
   * `fos_rest.routing.loader.directory`
   * `fos_rest.routing.loader.processor`
   * `fos_rest.routing.loader.reader.controller`
   * `fos_rest.routing.loader.reader.action`
   * `fos_rest.routing.loader.xml_collection`
   * `fos_rest.routing.loader.yaml_collection`
   * `fos_rest.serializer.exception_normalizer.jms`
   * `fos_rest.serializer.exception_normalizer.symfony`
   * `fos_rest.templating`

 * The following classes are marked as `internal` (backwards compatibility will no longer be guaranteed):

   * `FOS\RestBundle\DependencyInjection\Compiler\HandlerRegistryDecorationPass`
   * `FOS\RestBundle\DependencyInjection\FOSRestExtension`
   * `FOS\RestBundle\Form\Extension\DisableCSRFExtension`
   * `FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer`
   * `FOS\RestBundle\Normalizer\CamelKeysNormalizer`
   * `FOS\RestBundle\Normalizer\CamelKeysNormalizerWithLeadingUnderscore`
   * `FOS\RestBundle\Serializer\Normalizer\FormErrorHandler`
   * `FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer`
   * `FOS\RestBundle\Util\ExceptionValueMap`

 * The following classes are now `final`:

   * `FOS\RestBundle\Decoder\ContainerDecoderProvider`
   * `FOS\RestBundle\Decoder\JsonDecoder`
   * `FOS\RestBundle\Decoder\JsonToFormDecoder`
   * `FOS\RestBundle\Decoder\XmlDecoder`
   * `FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer`
   * `FOS\RestBundle\Negotiation\FormatNegotiator`
   * `FOS\RestBundle\Request\ParamFetcher`
   * `FOS\RestBundle\Request\ParamReader`
   * `FOS\RestBundle\Request\RequestBodyParamConverter`
   * `FOS\RestBundle\Response\AllowMethodsLoader\AllowedMethodsRouterLoader`
   * `FOS\RestBundle\Serializer\JMSSerializerAdapter`
   * `FOS\RestBundle\Serializer\SymfonySerializerAdapter`
   * `FOS\RestBundle\Version\ChainVersionResolver`
   * `FOS\RestBundle\Version\Resolver\HeaderVersionResolver`
   * `FOS\RestBundle\Version\Resolver\MediaTypeVersionResolver`
   * `FOS\RestBundle\Version\Resolver\QueryParameterVersionResolver`
   * `FOS\RestBundle\View\JsonpHandler`
   * `FOS\RestBundle\View\View`
   * `FOS\RestBundle\View\ViewHandler`

Upgrading From 2.7 To 2.8
=========================

 * The route generation feature is deprecated, disable it explicitly:

   ```yaml
   fos_rest:
       routing_loader: false
   ```

   You need to configure your routes explicitly or consider using the
   [RestRoutingBundle](https://github.com/handcraftedinthealps/RestRoutingBundle).

 * Support for serializing exceptions has been deprecated. Disable it by setting the
   `fos_rest.exception.serialize_exceptions` option to `false` and use the ErrorRenderer component
   instead.

   You can use the `flatten_exception_format` option to serialize exceptions according to the API
   Problem spec (RFC 7807):

   ```yaml
   fos_rest:
       exception:
           flatten_exception_format: 'rfc7807'
   ```

 * Deprecated returning anything other than `string` or `null` from `resolve()` when implementing
   the `VersionResolverInterface`.

 * Passing version numbers as integers to `Context::setVersion()` is deprecated. Strings will be
   enforced as of 3.0.

 * The `isFormatTemplating()`, `renderTemplate()`, and `prepareTemplateParameters()` methods of the
   `ViewHandler` class and the `ViewHandlerInterface` have been deprecated.

 * The constructor of the `ViewHandler` class has been deprecated. Use the static `create()` factory
   method instead.

 * The `setTemplateVar()`, `setPopulateDefaultVars()`, `getTemplateVar()`, and `isPopulateDefaultVars()`
   methods of the `Controller\Annotations\View` class have been deprecated.

 * The `setEngine()`, `setTemplate()`, `setTemplateData()`, `setTemplateVar()`, `getEngine()`,
   `getTemplate()`, `getTemplateData()`, and `getTemplateVar()` methods of the `View\View` class
   have been deprecated.

 * The `fos_rest.body_listener` option will change the default value from enabled to disabled in FOSRestBundle 3.0. Please enable or disable it explicitly.

 * The following options have been deprecated:

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

 * The following classes and interfaces are marked as `deprecated`, they will be removed in  3.0:

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

 * The following services and aliases are marked as `deprecated`, they will be removed in 3.0:

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

 * The following classes are marked as `internal` (backwards compatibility will no longer be guaranteed
   starting with FOSRestBundle 3.0):

   * `FOS\RestBundle\DependencyInjection\Compiler\HandlerRegistryDecorationPass`
   * `FOS\RestBundle\DependencyInjection\FOSRestExtension`
   * `FOS\RestBundle\Form\Extension\DisableCSRFExtension`
   * `FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer`
   * `FOS\RestBundle\Normalizer\CamelKeysNormalizer`
   * `FOS\RestBundle\Normalizer\CamelKeysNormalizerWithLeadingUnderscore`
   * `FOS\RestBundle\Serializer\Normalizer\FormErrorHandler`
   * `FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer`
   * `FOS\RestBundle\Util\ExceptionValueMap`

 * The following classes are marked as `final`. Extending them will not be supported as of 3.0:

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

CHANGELOG
=========

3.0.3
-----

* fixed being able to configure exception codes and messages based on interfaces (e.g.
  `Throwable`)

3.0.2
-----

* fixed the `ViewHandler` to not override an already set `status_code` in the serialization context
* fixed embedding status codes in the response body when a mapping of exception classes to status
  codes is configured

3.0.1
-----

* fixed handling requests without a content type inside the `RequestBodyParamConverter`
* `FlattenExceptionNormalizer` does no longer implement the `CacheableSupportsMethodInterface` to
  ensure compatibility with older versions of the Symfony Serializer component

3.0.0
-----

### Features

* added support for Symfony 5 compatibility

### BC Breaks

* the route generation feature was removed, setting it to another value than `false` leads to an
  exception
* support for serializing exceptions was removed, setting the `fos_rest.exception.serialize_exceptions`
  option to anything else than `false` leads to an exception
* support for returning anything other than `string` or `null` from `resolve()` when implementing
  the `VersionResolverInterface` was removed
* removed support for passing version numbers as integers to `Context::setVersion()`
* removed the `isFormatTemplating()`, `renderTemplate()`, and `prepareTemplateParameters()` methods
  from the `ViewHandler` class and the `ViewHandlerInterface`
* the constructor of the `ViewHandler` class is `private` now, use the static `create()` factory
  method instead
* removed the `setTemplateVar()`, `setPopulateDefaultVars()`, `getTemplateVar()`, and
  `isPopulateDefaultVars()` methods from the `Controller\Annotations\View` class
* removed the `setEngine()`, `setTemplate()`, `setTemplateData()`, `setTemplateVar()`, `getEngine()`,
  `getTemplate()`, `getTemplateData()`, and `getTemplateVar()` methods from the `View\View` class
* changed the default value of the `fos_rest.body_listener` option to `false`
* removed the `setMaxDepth()`/`getMaxDepth()` methods from the `Context` class, use
  `enableMaxDepth()`/`disableMaxDepth()` instead
* dropped support for Symfony components < 4.4
* removed the following options:

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

* removed the following classes and interfaces:

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

* removed the following services and aliases:

  * `fos_rest.access_denied_listener`
  * `fos_rest.exception_listener`
  * `fos_rest.exception.controller`
  * `fos_rest.exception.twig_controller`
  * `fos_rest.inflector`
  * `fos_rest.router`
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

* the following classes are marked as `internal` (backwards compatibility will no longer be guaranteed):

  * `FOS\RestBundle\DependencyInjection\Compiler\HandlerRegistryDecorationPass`
  * `FOS\RestBundle\DependencyInjection\FOSRestExtension`
  * `FOS\RestBundle\Form\Extension\DisableCSRFExtension`
  * `FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer`
  * `FOS\RestBundle\Normalizer\CamelKeysNormalizer`
  * `FOS\RestBundle\Normalizer\CamelKeysNormalizerWithLeadingUnderscore`
  * `FOS\RestBundle\Serializer\Normalizer\FormErrorHandler`
  * `FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer`
  * `FOS\RestBundle\Util\ExceptionValueMap`

* the following classes are now `final`:

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

2.8.3
-----

* fixed being able to configure exception codes and messages based on interfaces (e.g.
  `Throwable`)

2.8.2
-----

* fixed the `ViewHandler` to not override an already set `status_code` in the serialization context
* fixed embedding status codes in the response body when a mapping of exception classes to status
  codes is configured

2.8.1
-----

* fixed handling requests without a content type inside the `RequestBodyParamConverter`
* `FlattenExceptionNormalizer` does no longer implement the `CacheableSupportsMethodInterface` to
  ensure compatibility with older versions of the Symfony Serializer component

2.8.0
-----

### Features

* added a `SerializerErrorHandler` that leverages the `FOS\RestBundle\Serializer\Serializer` interface
  to hook into the error rendering process provided by the ErrorHandler component since Symfony 4.4
* added a new normalizer (for the Symfony serializer) and a new handler (for the JMS serializer) to
  serialize `FlattenException` instances, for backwards compatibility the resulting format by default
  is the same as was used for exceptions/errors before, use the `flatten_exception_format` to opt-in
  to a format compatible with the API Problem spec (RFC 7807):

   ```yaml
   fos_rest:
       exception:
           flatten_exception_format: 'rfc7807'
   ```
* added a new `ResponseStatusCodeListener` that maps exception/error codes to response status codes,
  enable it by setting the new `map_exception_codes` option to `true`

### Deprecations

* the route generation feature is deprecated, disable it explicitly:

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
* deprecated support for serializing exceptions, disable it by setting the `serialize_exceptions`
  option to false:

   ```yaml
   fos_rest:
       exception:
           serialize_exceptions: false
   ```
* deprecated returning anything other than `string` or `null` from `resolve()` when implementing   the `VersionResolverInterface`.
* deprecated support for passing version numbers as integers to `Context::setVersion()` (strings
  will be enforced as of 3.0)
* deprecated the `isFormatTemplating()`, `renderTemplate()`, and `prepareTemplateParameters()`
  methods of the `ViewHandler` class and the `ViewHandlerInterface`
* deprecated the constructor of the `ViewHandler` class, use the static `create()` factory method
  instead
* deprecated the `setTemplateVar()`, `setPopulateDefaultVars()`, `getTemplateVar()`, and
  `isPopulateDefaultVars()` methods of the `Controller\Annotations\View` class
* deprecated the `setEngine()`, `setTemplate()`, `setTemplateData()`, `setTemplateVar()`, `getEngine()`,
  `getTemplate()`, `getTemplateData()`, and `getTemplateVar()` methods of the `View\View` class
* deprecated not enabling the `fos_rest.body_listener` option explicitly, it will be disabled by default
  in 3.0
* deprecated the following options:

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

* the following classes and interfaces are marked as `deprecated`, they will be removed in  3.0:

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

* the following services and aliases are marked as `deprecated`, they will be removed in  3.0:

  * `fos_rest.access_denied_listener`
  * `fos_rest.exception_listener`
  * `fos_rest.exception.controller`
  * `fos_rest.exception.twig_controller`
  * `fos_rest.inflector`
  * `fos_rest.router`
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

* the following classes are marked as `internal` (backwards compatibility will no longer be guaranteed
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

* the following classes are marked as `final` (extending them will not be supported as of 3.0):

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

2.7.4
-----

* fixed compatibility with JMS Serializer with explicitly disabled max
  depth checks (#2060)
* fixed config validation when mapping `Throwable` instances of classes
  that do not extend PHP's `Exception` class (#2131)

2.7.3
-----

* harden the `JsonToFormDecoder` to not error on non-array input (#2145)

2.7.2
-----

* fixed serializing Error instances when the Symfony Serializer is used (#2110)
* fixed serializing Error instances when JMS Serializer is used (#2105)
* fixed compatibility with `null` owner returned by SensioFrameworkExtraBundle (#2097)
* completely fixed handling `Throwable` objects in `ExceptionController::showAction()`,
  continues #2093 (#2096)

2.7.1
-----

* fixed handling all `Throwable` objects in `ExceptionController::showAction()` (#2093)
* fixed `ViewHandlerInterface` alias definition (#2085)

2.7.0
-----

* ignore `SessionInterface` and `UserInterface` controller action arguments
* fixed `ExceptionListener` deprecation warning
* fixed `ControllerNameParser` deprecation warning
* fixed `DisableCSRFExtension::getExtendedTypes()` return type
* improved `EngineInterface` error message in `ViewHandler`
* improved Symfony 4.4 compatibility
* automatically use Twig as templating engine when available

2.6.0
-----

* ensure compatibility with the `FlattenException` from the new ErrorRenderer component
* fix handling the `serialize_null` option with the Symfony serializer
* added support for using multiple constraints for the `requirements` option of the `@RequestParam`
  annotation
* drop support for PHP 5.5, 5.6 and 7.0
* drop support for SF 4.0, 4.1 and 4.2 (3.4 LTS is still supported)
* deprecated using the `ParamFetcher` class without passing a validator as the third argument, this
  argument will become mandatory in 3.0
* fix compatiblity without the deprecated templating in Symfony 4.3; see #2012 on how to configure the FOSRestBundle
* removed symfony/templating from the dependencies; if you still use it you need to require it in your app

2.5.0
-----

* compatibility with Symfony 4.2
* deprecated the `FOSRestController` base class, use the new `AbstractFOSRestController` instead
* dropped support for Symfony 2.7 to 3.3
* compatibility with JMS Serializer 2 and JMSSerializerBundle 3
* overwrite rules when they are defined in different config files instead of throwing exceptions
* fixed using the `nullable` option of the param annotations when the `map` option is enabled
* ensure a predictable order of routes by sorting controllers by name when loading classes from a directory
* reset the internal state of the view handler to fix compatibility with PHP-PM
* fix different bugs related to the handling of API versions (see #1491, #1529, #1691)

2.4.0
-----

* [BC BREAK] The `@Route` annotation and all its children no longer extend SensioFrameworkExtraBundle's annotation.
  The main effect is that `@Route::$service` is no longer available. Instead, define your controllers using the FQCN
  as service IDs or create an alias in the container using the FQCN.

2.3.1
-----

* improved Symfony 4 compatibility

* manually decorate the core JMS handler registry

* run checks after SensioFrameworkExtraBundle

* made the view handler alias public

* check for definitions before they might be removed

* added Yaml routing resource support

* refactored several unit tests

2.3.0
-----

* added support for file paths to the directory route loader

* added support for context factories when using JMS Serializer

* the `RequestBodyParamConverter` ignores unrelated controller arguments to not conflict with Symfony's built-in
  argument resolver

* made the bundle compatible with SensioFrameworkExtraBundle 4.x

* added some interface aliases to support by ID autowiring

* added support for custom keys for groups when using JMSSerializerBundle

* allow to load FOSRestBundle inside the kernel before JMSSerializerBundle

* added the `fos_rest.routing_loader.prefix_methods` option to disable method name prefixes in generated route names

* removed newline characters from exception messages

1.8.0
-----

* added a new `InvalidParameterException` as a specialization of the `BadRequestHttpException`

* deprecated the `FOS\RestBundle\Util\ViolationFormatter` class and the
  `FOS\RestBundle\Util\ViolationFormatterInterface`

* deprecated the `ViolationFormatterInterface` argument of the `ParamFetcher` class constructor

* deprecated the `RedirectView` and `RouteRedirectView` classes, use `View::createRedirect()` and
  `View::createRouteRedirect()` instead

* added a `fos_rest.exception.debug` config option that defaults to the `kernel.debug` container
  parameter and can be turned on to include the caught exception message in the exception controller's
  response

* introduced the concept of REST zones which makes it possible to disable all REST listeners
  when a request matches certain attributes

* fixed that serialization groups are always passed to the constructor as an array

* added annotations to support additional HTTP methods defined by RFC 2518 (WebDAV)

* added a new loader that allows to extract REST routes from all controller classes from a
  directory

* introduced a serializer adapter layer to ease the integration of custom serialization
  implementations

* deprecated the getter methods of the `ViewHandler` class

* fixed an issue that prevented decoration of the `TemplateReferenceInterface` from the Symfony
  Templating component

* fixed: no longer overwrite an explicitly configured template in the view response listener

* added support for API versioning in URL parameters, the `Accept` header or using a custom header

* marked some classes and methods as internal, do no longer use them in your code as they are likely
  to be removed in future releases

* deprecated the `DoctrineInflector` class and the `InflectorInterface` from the
  `FOS\RestBundle\Util\Inflector`in favor of their replacements in the `FOS\RestBundle\Inflector`
  namespace

* deprecated the `FormatNegotiator` class and the `FormatNegotiatorInterface` from the
  `FOS\RestBundle\Util` namespace in favor of the new `FOS\RestBundle\Negotiation\FormatNegotiator`
  class

* deprecated the `FOS\RestBundle\Util\MediaTypeNegotiatorInterface` which should no longer be used

1.7.9
-----

* handle `\Throwable` instances in the `ExceptionController`

* fixed that the default exclusion strategy groups for the serializer are not the empty string

* fixed a BC break that prevented the `CamelKeysNormalizer` from removing leading underscores

* fixed the `AllowedMethodsRouteLoader` to work with Symfony 3.0

1.7.8
-----

* removed uses of the reflection API in favor of faster solutions when possible

* fixed the configuration to use serialization groups and versions at the same time

1.7.7
-----

* when using Symfony 3.x, the bundle doesn't call methods anymore that have been deprecated in
  Symfony 2.x and were removed in Symfony 3.0

* the `ViewResponseListener` does not overwrite explicitly configured templates anymore

* fixed the `ParamFetcher` class to properly handle sub requests

1.7.6
-----

* added a `CamelKeysNormalizerWithLeadingUnderscore` that keeps leading underscores when
  converting snake case to camel case (for example, leaving `_username` unchanged)

1.7.5
-----

**CAUTION:** Accidentally, this patch release was never published.

1.7.4
-----

* removed some code from the `ViewResponseListener` class that was already present in the parent
  `TemplateListener` class

1.7.3
-----

* made it possible to use the bundle with Symfony 3.x and fixed some compatibility issues with
  Symfony 3.0

* fixed the exception controller to return a 406 (Not Acceptable) response when the format
  negotiator throws an exception

1.7.2
-----

* fixed loading XML schema definition files in case the paths contain special characters (like
  spaces)

* return the FQCN in the form type extension's `getExtendedType()` method to be compatible with
  Symfony >= 2.8

* added the `extended-type` attribute to the `form.type_extension` tag to be compatible with
  Symfony >= 2.8

* fixed some code examples in the documentation

* fixed exception message when using non-numeric identifiers (like UUID or GUID)

* allow version 1.x of `jms/serializer` and `jms/serializer-bundle`

* allow to use the Symfony serializer even if the JMS serializer is present

1.7.1
-----

* fix regression when handling methods in `@Route` annotations

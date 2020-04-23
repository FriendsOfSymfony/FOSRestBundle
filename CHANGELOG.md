CHANGELOG
=========

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

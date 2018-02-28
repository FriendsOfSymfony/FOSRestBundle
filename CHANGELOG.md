CHANGELOG
=========

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

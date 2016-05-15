CHANGELOG
=========

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

CHANGELOG
=========

1.7.8
-----

1.7.7
-----

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

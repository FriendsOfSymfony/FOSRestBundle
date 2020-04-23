Upgrading From 2.x To 3.0
=========================

 * Enabling the `fos_rest.routing_loader` option is not supported anymore. Setting
   it to another value than `false` leads to an exception.

 * The `fos_rest.exception.exception_controller`, `fos_rest.exception.exception_listener`, and
   `fos_rest.exception.service` options have been removed.

 * `Context::setVersion()` does not accept integers anymore.

 * The `ExceptionValueMap` class is `final`. Extending it is no longer supported. The `resolveThrowable()`
   method has been removed.

 * The `isFormatTemplating()`, `renderTemplate()`, and `prepareTemplateParameters()` methods of the
   `ViewHandler` class and the `ViewHandlerInterface` have been removed.

 * The constructor of the `ViewHandler` class has been changed to `private`. Use the static `create()`
   factory method instead.

 * The `setTemplateVar()`, `setPopulateDefaultVars()`, `getTemplateVar()`, and `isPopulateDefaultVars()`
   methods of the `Controller\Annotations\View` class have been removed.

 * The `setEngine()`, `setTemplate()`, `setTemplateData()`, `setTemplateVar()`, `getEngine()`,
   `getTemplate()`, `getTemplateData()`, and `getTemplateVar()` methods of the `View\View` class
   have been removed.

 * The `fos_rest.templating` alias has been removed.

 * The `fos_rest.view.templating_formats` option has been removed.

 * The default values of the `fos_rest.service.templating` and `fos_rest.view.default_engine` options
   have been changed to `null`. Setting it to another value leads to an exception.

 * The default value of the `fos_rest.view.force_redirects` option has been changed to the empty
   array. Setting it to another value leads to an exception.

 * The `TemplatingExceptionController` and the `TwigExceptionController` classes
   have been removed.

 * The `fos_rest.exception.twig_controller` service has been removed.

 * Support for passing a `ControllerNameParser` instance as the third argument to
   the constructor of the `RestRouteLoader` class has been removed.

 * The `setMaxDepth()` method has been removed from the `Context` class. Use the
   `enableMaxDepth()` and `disableMaxDepth()` methods instead.

 * The `getMaxDepth()` method has been removed from the `Context` class. Use the
   `isMaxDepthEnabled()` method instead.

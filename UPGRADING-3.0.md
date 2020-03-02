Upgrading From 2.x To 3.0
=========================

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

 * The default value of the `fos_rest.exception.exception_controller` option has
   been changed to `fos_rest.exception.controller::showAction`.

 * The `TemplatingExceptionController` and the `TwigExceptionController` classes
   have been removed.

 * The `fos_rest.exception.twig_controller` service has been removed.

 * Support for passing a `ControllerNameParser` instance as the third argument to
   the constructor of the `RestRouteLoader` class has been removed.

 * The default value of the `fos_rest.routing_loader.parse_controller_name` option
   has been changed to `false`. Setting it to another value leads to an exception.

 * The `setMaxDepth()` method has been removed from the `Context` class. Use the
   `enableMaxDepth()` and `disableMaxDepth()` methods instead.

 * The `getMaxDepth()` method has been removed from the `Context` class. Use the
   `isMaxDepthEnabled()` method instead.

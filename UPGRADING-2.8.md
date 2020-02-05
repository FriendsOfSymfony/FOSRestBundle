Upgrading From 2.7 To 2.8
=========================

 * Not configuring the `fos_rest.exception.exception_controller` option is deprecated. Its default
   value will be changed to `fos_rest.exception.controller::showAction` in 3.0.

 * The `TemplatingExceptionController` and the `TwigExceptionController` classes have been deprecated.

 * The `fos_rest.exception.twig_controller` service has been deprecated.

 * Not passing a `RestControllerReader` instance as the third argument to the constructor of the
   `RestRouteLoader` class is deprecated. Support for passing a `ControllerNameParser` instance
   will be removed in 3.0.

   Set the `fos_rest.routing_loader.parse_controller_name` option to `false` to opt-out:

   ```yaml
   fos_rest:
       routing_loader:
           parse_controller_name: false
   ```

   The default value for this option is `true`. Not setting it to `false` is deprecated and will
   result in an exception in 3.0.

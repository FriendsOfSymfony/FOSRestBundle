Upgrading From 2.x To 3.0
=========================

 * Support for passing a `ControllerNameParser` instance as the third argument to
   the constructor of the `RestRouteLoader` class has been removed.

 * The default value of the `fos_rest.routing_loader.parse_controller_name` option
   has been changed to `false`. Setting it to another value leads to an exception.

 * The `setMaxDepth()` method has been removed from the `Context` class. Use the
   `enableMaxDepth()` and `disableMaxDepth()` methods instead.

 * The `getMaxDepth()` method has been removed from the `Context` class. Use the
   `isMaxDepthEnabled()` method instead.

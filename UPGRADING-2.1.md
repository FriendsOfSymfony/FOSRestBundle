Upgrading From 2.0 To 2.1
=========================

 * The `setMaxDepth()` method from the `Context` class is deprecated. Use the
   `enableMaxDepth()` and `disableMaxDepth()` methods instead.

 * The `getMaxDepth()` method from the `Context` class is deprecated. Use the
   `isMaxDepthEnabled()` method instead.

 * The `getGroups` method from the `Context` class can return null when no group have been added.

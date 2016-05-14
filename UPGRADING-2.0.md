Upgrading From 1.x To 2.0
=========================

 * The `RedirectView` and `RouteRedirect` view classes were removed. Use `View::createRedirect()`
   and `View::createRouteRedirect()` instead.

   **Note**: the default status code for a route redirect has changed from HTTP_CREATED (201) to
   HTTP_FOUND (302).

 * The `FOS\RestBundle\Util\ViolationFormatter` class and the `FOS\RestBundle\Util\ViolationFormatterInterface`
   were removed. Catch specialized exception classes instead of checking specific exception messages.

 * The `ViolationFormatterInterface` argument of the constructor of the `ParamFetcher` class was
   removed.

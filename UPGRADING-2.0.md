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

 * The SensioFrameworkExtraBundle view annotations must be enabled to use the `ViewResponseListener`:

   ```yaml
   # app/config/config.yml
   sensio_framework_extra:
       view:
           annotations: true
   ```

 * dropped support for the legacy ``Symfony\Component\Validator\ValidatorInterface``

 * removed ``FOS\RestBundle\Util\Codes`` in favor of ``Symfony\Component\HttpFoundation\Response``

 * compatibility with Symfony <2.7, JMS Serializer/SerializerBundle <1.0 and SensioFrameworkExtraBundle <3.0 was dropped

 * RedirectView and RouteRedirectView view were removed. Use View::createRedirect and
   View::createRouteRedirect instead. Note: the default status code for a route redirect
   has changed from HTTP_CREATED (201) to HTTP_FOUND (302).

 * constructor signature of DisableCSRFExtension was changed

 * constructor signatures of most of the classes which used the container were changed

 * removed ``callback_filter`` configuration option for the jsonp_handler

 * ``exception_wrapper_handler`` is now the name of a service and not the name of a class

 * removed all ``.class`` parameters, instead overwriting services via explicit Bundle configuration is preferred

 * renamed ``AbstractScalarParam::$array`` to ``AbstractScalarParam::$map``

 * added `ControllerTrait` for developers that prefer to use DI for their controllers instead of extending ``FOSRestController``

 * when having an action called ``lockUserAction``, then it will have to use the http method ``LOCK`` (RFC-2518) instead of ``PATCH``. The following methods are affected by this change
   * ``COPY``
   * ``LOCK``
   * ``MKCOL``
   * ``MOVE``
   * ``PROPFIND``
   * ``PROPPATCH``
   * ``UNLOCK``

 * removed the ability of the ``AccessDeniedListener`` to render a response. Use the FOSRestBundle or the twig exception controller in complement.

 * changed the priority of ``RequestBodyParamConverter`` to ``-50``

 * made silent the ``RequestBodyParamConverter`` when a parameter is optional and it can't resolve it

 * removed the ``format_negotiator`` option ``exception_fallback_format``; you can match the ``ExceptionController`` thanks to the ``attributes`` option instead

 * `View::setSerializationContext` and `View::getSerializationContext` have been removed. Use `View::setContext` and `View::getContext` together with the new Context class instead.

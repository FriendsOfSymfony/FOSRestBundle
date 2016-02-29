Upgrading
=========

This document will be updated to list important BC breaks and behavioral changes.

### upgrading to 2.0.0

 * dropped support for the legacy ``Symfony\Component\Validator\ValidatorInterface``
 * removed ``FOS\RestBundle\Util\Codes`` in favor of ``Symfony\Component\HttpFoundation\Response``
 * compatibility with Symfony <2.7, JMS Serializer/SerializerBundle <1.0 and SensioFrameworkExtraBundle <3.0 was dropped
 * RedirectView and RouteRedirectView view were removed. Use View::createRedirect and
   View::createRouteRedirect instead. Note: the default status code for a route redirect
   has changed from HTTP_CREATED (201) to HTTP_FOUND (302).
 * constructor signature of DisableCSRFExtension was changed
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

### upgrading from 1.5.*

  * Dropped support for Symfony 2.2 (which includes dropping support for "pattern" in favor of only supporting "path" in routes), see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/952
  * Dropped support for SensioFrameworkExtraBundle 2.x, see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/952
    (support for SensioFrameworkExtraBundle was added back in version 1.6.1 of the FOSRestBundle)

### upgrading from 1.4.*

  * In JsonToFormDecoder prefer to transform false data to null, see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/883
  * Routing name is no longer appended to generic route name, see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/879

### upgrading from 1.3.*

 * [`ViewHandler::getSerializationContext`](https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/View/ViewHandler.php) is now a `protected` method instead of `public`.
 * BodyListener priority has been reverted back to 10 (see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/763)

### upgrading from 1.0.0-RC1

 * The Bundle no longer depends on "friendsofsymfony/rest" and as a result several class names have changed.
   Specifically ``FOS\Rest\Util\Codes`` is now ``FOS\RestBundle\Util\Codes`` and also the sub-namespace for
   the decoders has changed from ``FOS\Rest\Decoder`` to ``FOS\RestBundle\Decoder``. In practice it should be
   sufficient to simply search replace ``FOS\Rest\`` with ``FOS\RestBundle\`.
 * The XmlDecoder now has a dependency on "symfony/serializer"

### upgrading from 0.13.1

 * ExceptionController::showAction() doesn't have type hint on the $exception object anymore due to a BC change
   in symfony/symfony 2.3.5, see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/565

 * POST routes now pluralize the resource name, ie. /users vs. /user

 * The response for non-valid Forms has changed. See http://symfony.com/doc/master/bundles/FOSRestBundle/2-the-view-layer.html#forms-and-views

 * The default error message structure has changed.

    _Old structure_

        {
          "status": "error",
          "status_code": 400,
          "status_text": "Bad Request",
          "current_content": "",
          "message": "New comment is not valid."
        }

    _New structure_

        {
          "code": 400,
          "message": "New comment is not valid.";
        }

    _Alternatively you can inject your own implementation. See http://symfony.com/doc/master/bundles/FOSRestBundle/2-the-view-layer.html#forms-and-views_

 * The ``format_listener`` configuration has changed to allow different settings per host/path.
   Finally the signature of FormatNegotiatorInterface::getBestFormat() changed.

### upgrading from 0.12.0

* Route parameters cannot be set via setData anymore, please use a dedicated function `setRouteParameters` from now on

### upgrading from 0.11.0

* now requires JMSSerializerBundle 0.12
* refactored the View class to drop the serializer specific methods in favor of setSerializationContext()
* default version/groups will now only be applied if no SerializationContext was explicitly set on the View

### upgrading from 0.10.0

* now requires JMSSerializerBundle 1.0 (later renamed to 0.11) which is not compatible with Symfony2.0

### upgrading from 0.9.0

 * the view response listener is now disabled by default. See [enable view listener](http://symfony.com/doc/master/bundles/FOSRestBundle/3-listener-support.html#view-response-listener) for how to enable it.
 * JMSSerializerBundle is now an optional dependency and therefore needs to be added explicitly

### upgrading from 0.7.0

 * renamed "query" fetcher to "param" fetcher, this affects the configuration as well as the name of interfaces and request attributes
 * ViewHandler now only calls "createView()" in a single form instance set as "form" in the data array
 * removed "serializer_version" config option on favor of "serializer: ['version': ..]"

### upgrading from 0.6.0

 * renamed [get|set]Objects*() to [get|set]Serializer*()
 * renamed the "objects_version: XXX" configuration option to "serializer: [version: XXX]"
 * moved serializer configuration code from ViewHandler::createResponse() to ViewHandler::getSerializer()
 * made ViewHandler::getSerializer() protected again

### 19 April 2012

 * Change route fallback action to PATCH instead of POST

 Automatically generated routes will now fall back to the PATCH instead of the POST method.

 More information in the docs, at [this issue](https://github.com/FriendsOfSymfony/FOSRestBundle/issues/223) and [this PR](https://github.com/FriendsOfSymfony/FOSRestBundle/pull/224).

### upgrading from 0.5.0_old_serializer

 * The ViewInterface is gone so you might have to change your controller config if you refer to the fos_rest.view service.

 * The View class is now split into a View (simple data container) and a ViewHandler (contains the actual rendering logic).

    The following code would need to be changed:

        public function indexAction($name = null)
        {
            $view = $this->container->get('fos_rest.view');

            if (!$name) {
                $view->setResourceRoute('_welcome');
            } else {
                $view->setParameters(array('name' => $name));
                $view->setTemplate(new TemplateReference('LiipHelloBundle', 'Hello', 'index'));
            }

            return $view->handle();
        }

    To the following code:

        public function indexAction($name = null)
        {
            if (!$name) {
                $view = \FOS\RestBundle\View\RouteRedirectView::create('_welcome');
            } else {
                $view = \FOS\RestBundle\View\View::create(array('name' => $name))
                    ->setTemplate(new TemplateReference('LiipHelloBundle', 'Hello', 'index'));
                ;
            }

            return $this->container->get('fos_rest.view_handler')->handle($view);
        }

  * The custom Serializer class was removed instead JMSSerializerBundle is now used, which
    replaces the concept of normalizers/encoders with the concept of visitors and handler

Upgrading
=========

Note as FOSRestBundle is not yet declared stable, this document will be updated to
list important BC breaks.

### upgrading from 0.13.1

 * POST routes now pluralize the resource name, ie. /users vs. /user

 * The response for non-valid Forms has changed. See https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/2-the-view-layer.md#forms-and-views

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

    _Alternatively you can inject your own implementation. See https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/2-the-view-layer.md#forms-and-views_

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

 * the view response listener is now disabled by default. See [enable view listener](https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/3-listener-support.md#view-response-listener) for how to enable it.
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

Step 2: The view layer
======================

### Introduction

The view layer makes it possible to write `format` (html, json, xml, etc) agnostic
controllers, by placing a layer between the Controller and the generation of the
final output via the templating or a serializer.

The Bundle works with both the Symfony2 core serializer:
http://symfony.com/doc/current/components/serializer.html

But also with the more sophisticated serializer by Johannes Schmitt:
https://github.com/schmittjoh/serializer
https://github.com/schmittjoh/JMSSerializerBundle

In your controller action you will then need to create a ``View`` instance that
is then passed to the ``fos_rest.view_handler`` service for processing. The
``View`` is somewhat modeled after the ``Response`` class, but as just stated
it simply works as a container for all the data/configuration for the
``ViewHandler`` class for this particular action.  So the ``View`` instance
must always be processed by a ``ViewHandler`` (see the below section on the
"view response listener" for how to get this processing applied automatically)

FOSRestBundle ships with a controller extending the default Symfony controller,
which adds several convenience methods:

```php
<?php

use FOS\RestBundle\Controller\FOSRestController;

class UsersController extends FOSRestController
{
    public function getUsersAction()
    {
        $data = ...; // get data, in this case list of users.
        $view = $this->view($data, 200)
            ->setTemplate("MyBundle:Users:getUsers.html.twig")
            ->setTemplateVar('users')
        ;

        return $this->handleView($view);
    }

    public function redirectAction()
    {
        $view = $this->redirectView($this->generateUrl('some_route'), 301);
        // or
        $view = $this->routeRedirectView('some_route', array(), 301);

        return $this->handleView($view);
    }
}
```

To simplify this even more: If you rely on the ``ViewResponseListener`` in combination with
SensioFrameworkExtraBundle you can even omit the calls to ``$this->handleView($view)``
and directly return the view objects. See chapter 3 on listeners for more details
on the View Response Listener.

As the purpose is to create a format-agnostic controller, data assigned to the
``View`` instance should ideally be an object graph, though any data type is
acceptable. Note that when rendering templating formats, the ``ViewHandler``
will wrap data types other than associative arrays in an associative array with
a single key (default  ``'data'``), which will become the variable name of the
object in the respective template. You can change this variable by calling
the ``setTemplateVar()`` method on the view object.

There are also two specialized ``View`` classes for handling redirects, one for
redirecting to an URL called ``RedirectView`` and one to redirect to a route
called ``RouteRedirectView``.  Note that whether these classes actually cause a
redirect or not is determined by the ``force_redirects`` configuration option,
which is only enabled for ``html`` by default (see below).

There are several more methods on the ``View`` class, here is a list of all
the important ones for configuring the view:

* ``setData($data)`` - Set the object graph or list of objects to serialize.
* ``setHeader($name, $value)`` - Set a header to put on the HTTP response.
* ``setHeaders(array $headers)`` - Set multiple headers to put on the HTTP response.
* ``setSerializationContext($context)`` - Set the serialization context to use.
* ``setTemplate($name)`` - Name of the template to use in case of HTML rendering.
* ``setTemplateVar($name)`` - Name of the variable the data is in, when passed to HTML template. Defaults to ``'data'``.
* ``setEngine($name)`` - Name of the engine to render HTML template. Can be autodetected.
* ``setFormat($format)`` - The format the response is supposed to be rendered in. Can be autodetected using HTTP semantics.
* ``setLocation($location)`` - The location to redirect to with a response.
* ``setRoute($route)`` - The route to redirect to with a response.
* ``setRouteParameters($parameters)`` - Set the parameters for the route.
* ``setResponse(Response $response)`` - The response instance that is populated by the ``ViewHandler``.

See the following example code for more details:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/HelloController.php

### Forms and Views

Symfony Forms have special handling inside the view layer. Whenever you

- return a Form from the controller
- Set the form as only data of the view
- return an array with a 'form' key, containing a form
- return a form with validation errors

Then:

- If the form is bound and no status code is set explicitly, an invalid form leads to a "validation failed" response.
- In a rendered template, the form is passed as 'form' and ``createView()`` is called automatically.
- ``$form->getData()`` is passed into the view as template as ``'data'`` if the form is the only view data.
- An invalid form will be wrapped into an exception

A response example of an invalid form:

```javascript
{
  "code": 400,
  "message": "Validation Failed";
  "errors": {
    "children": {
      "username": {
        "errors": [
          "This value should not be blank."
        ]
      }
    }
  }
}
```

If you don't like the default exception structure, you can provide your own implementation.

_Implement the ExceptionWrapperHandlerInterface_:

``` php
namespace My\Bundle\Handler;

class MyExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return new MyExceptionWrapper($data);
    }
}
```

In the `wrap` method return any object or array

_Update the config.yml_:

``` yaml
fos_rest:
    view:
        ...
        exception_wrapper_handler: My\Bundle\Handler\MyExceptionWrapperHandler
        ...
```

### Configuration

The ``formats`` and ``templating_formats`` settings determine which formats are
respectively supported by the serializer and by the template layer. In other
words any format listed in ``templating_formats`` will require a template for
rendering using the ``templating`` service, while any format listed in
``formats`` will use the serializer for rendering.  For both settings a
value of ``false`` means that the given format is disabled.

When using ``RouteRedirectView::create()`` the default behavior of forcing a
redirect to the route for html is enabled, but needs to be enabled for other
formats if needed.

Finally the HTTP response status code for failed validation defaults to
``400``. Note when changing the default you can use name constants of
``FOS\RestBundle\Util\Codes`` class or an integer status code.

You can also set the default templating engine to something different than the
default of ``twig``:

```yaml
# app/config/config.yml
fos_rest:
    view:
        formats:
            rss: true
            xml: false
        templating_formats:
            html: true
        force_redirects:
            html: true
        failed_validation: HTTP_BAD_REQUEST
        default_engine: twig
```

See the following example configuration for more details:
https://github.com/liip-forks/symfony-standard/blob/techtalk/app/config/config.yml

### Custom handler

While many things should be possible via the serializer in some cases
it might not be enough. For example you might need some custom logic to be
executed in the ``ViewHandler``. For these cases one might want to register a
custom handler for a specific format. The custom handler can either be
registered by defining a custom service, via a compiler pass or it can even be
registered from inside the controller action.

The callable will receive 3 parameters:

 * the instance of the ``ViewHandler``
 * the instance of the ``View``
 * the instance of the ``Request``

Note there are several public methods on the ``ViewHandler`` which can be helpful:

 * ``isFormatTemplating()``
 * ``createResponse()``
 * ``createRedirectResponse()``
 * ``renderTemplate()``

There is an example inside LiipHelloBundle to show how to register a custom handler:
https://github.com/liip/LiipHelloBundle/blob/master/View/RSSViewHandler.php
https://github.com/liip/LiipHelloBundle/blob/master/Resources/config/config.yml

There is another example in ``Resources\doc\examples``:
https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/examples/RssHandler.php

Here is an example using a closure registered inside a Controller action:

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\View\View;

class UsersController extends Controller
{
    public function getUsersAction()
    {
        $view = View::create();

        ...

        $handler = $this->get('fos_rest.view_handler');
        if (!$handler->isFormatTemplating($view->getFormat())) {
            $templatingHandler = function($handler, $view, $request) {
                // if a template is set, render it using the 'params' and place the content into the data
                if ($view->getTemplate()) {
                    $data = $view->getData();
                    if (empty($data['params'])) {
                        $params = array();
                    } else {
                        $params = $data['params'];
                        unset($data['params']);
                    }
                    $view->setData($params);
                    $data['html'] = $handler->renderTemplate($view, 'html');

                    $view->setData($data);
                }
                return $handler->createResponse($view, $request, $format);
            };
            $handler->registerHandler($view->getFormat(), $templatingHandler);
        }
        return $handler->handle($view);
    }
}
```

#### Jsonp custom handler

To enable the common use case of creating Jsonp responses this Bundle provides an
easy solution to handle a custom handler for this use case. Enabling this setting
also automatically uses the mime type listener (see the next chapter) to register
a mime type for Jsonp.

Simply add the following to your configuration

```yaml
# app/config/config.yml
fos_rest:
    view:
        jsonp_handler: ~
```

It is also possible to customize both the name of the GET parameter with the callback,
as well as the filter pattern that validates if the provided callback is valid or not.

```yaml
# app/config/config.yml
fos_rest:
    view:
        jsonp_handler:
           callback_param:       mycallback
```

Finally the filter can also be disabled by setting it to false.

```yaml
# app/config/config.yml
fos_rest:
    view:
        jsonp_handler:
            callback_param:       false
```

When working with JSONP, be aware of
[CVE-2014-4671](http://web.nvd.nist.gov/view/vuln/detail?vulnId=CVE-2014-4671)
(full explanation can be found here: [Abusing JSONP with Rosetta
Flash](http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/)). You
SHOULD use
[NelmioSecurityBundle](https://github.com/nelmio/NelmioSecurityBundle) and
[disable the content type sniffing for script
resources](https://github.com/nelmio/NelmioSecurityBundle#content-type-sniffing).

#### CSRF validation

When building a single application that should handle forms both via HTML forms as well
as via a REST API, one runs into a problem with CSRF token validation. In most cases it
is necessary to enable them for HTML forms, but it makes no sense to use them for a REST
API. For this reason there is a form extension to disable CSRF validation for users
with a specific role. This of course requires that REST API users authenticate themselves
and get a special role assigned.

```yaml
fos_rest:
    disable_csrf_role: ROLE_API
```

## That was it!
[Return to the index](index.md) or continue reading about [Listener support](3-listener-support.md).

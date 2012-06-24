Step 2: The view layer
======================
### Introduction

The view layer makes it possible to write format agnostic controllers, by
placing a layer between the Controller and the generation of the final output
via the templating or JMSSerializerBundle.

In your controller action you will then need to create a ``View`` instance that
is then passed to the ``fos_rest.view_handler`` service for processing. The
``View`` is somewhat modeled after the ``Response`` class, but as just stated
it simply works as a container for all the data/configuration for the
``ViewHandler`` class for this particular action.  So the ``View`` instance
must always be processed by a ``ViewHandler`` (see the below section on the
"view response listener" for how to get this processing applied automatically)

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\View\View;

class UsersController extends Controller
{
    public function getUsersAction()
    {
        $view = View::create()
          ->setStatusCode(200)
          ->setData($data);

        ...

        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
```

In the above example, ``View::create`` is a simple, convenient method to allow
for a fluent interface. It is equivalent to instantiating a View by calling its
constructor.

As the purpose is to create a format-agnostic controller, data assigned to the
``View`` instance should ideally be an object graph, though any data type is
acceptable. Note that when rendering templating formats, the ``ViewHandler``
will wrap data types other than associative arrays in an associative array with
a single key (default  ``'data'``), which will become the variable name of the
object in the respective template.

There are also two specialized ``View`` classes for handling redirects, one for
redirecting to an URL called ``RedirectView`` and one to redirect to a route
called ``RouteRedirectView``.  Note that whether these classes actually cause a
redirect or not is determined by the ``force_redirects`` configuration option,
which is only enabled for ``html`` by default (see below).

See the following example code for more details:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/HelloController.php

### Configuration

The ``formats`` and ``templating_formats`` settings determine which formats are
respectively supported by the serializer and by the template layer. In other
words any format listed in ``templating_formats`` will require a template for
rendering using the ``templating`` service, while any format listed in
``formats`` will use JMSSerializerBundle for rendering.  For both settings a
value of ``false`` means that the given format is disabled.

When using ``RouteRedirectView::create()`` the default behavior of forcing a
redirect to the route for html is enabled, but needs to be enabled for other
formats if needed.

Finally the HTTP response status code for failed validation defaults to
``400``. Note when changing the default you can use name constants of
``FOS\Rest\Util\Codes`` class or an integer status code.

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
https://github.com/lsmith77/symfony-standard/blob/techtalk/app/config/config.yml

### Custom handler

While many things should be possible via the JMSSerializerBundle in some cases
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
            }
            $handler->registerHandler($view->getFormat(), $templatingHandler);
        }
        return $handler->handle($view);
    }
}
```

## That was it!
[Return to the index](index.md) or continue reading about [Listener support](3-listener-support.md).

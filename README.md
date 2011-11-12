RestBundle
==========

This bundle provides various tools to rapidly develop RESTful API's & applications with Symfony2.

It is currently under development so key pieces that are planned are still missing.
See here for more details on what is planned:
https://github.com/FriendsOfSymfony/FOSRestBundle/issues

For now the Bundle provides a view layer to enable output (including redirects) and
format agnostic Controllers (using the JMSSerializerBundle for serialization
of formats that do not use template).

Furthermore a custom route loader can be used when following a method naming convention.
It will automatically provide routes for multiple actions by simply
configuring the name of a controller.

It also has support for RESTful decoding of HTTP request body and Accept headers
as well as a custom Exception controller that assists in using appropriate HTTP
status codes.

[![Build Status](https://secure.travis-ci.org/FriendsOfSymfony/FOSRestBundle.png)](http://travis-ci.org/FriendsOfSymfony/FOSRestBundle)

Installation
============

### Add this bundle to your project

**Using the vendors script**

Add the following lines in your deps file:

    [FOSRestBundle]
        git=git://github.com/FriendsOfSymfony/FOSRestBundle.git
        target=bundles/FOS/RestBundle

You will also need to install the 
[JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle). This bundle is used 
for serialization. Please see the bundle's [documentation](https://github.com/schmittjoh/JMSSerializerBundle/blob/master/Resources/doc/index.rst) 
for configuration instructions. Add these lines to your deps file:

    [JMSSerializerBundle]
        git=git://github.com/schmittjoh/JMSSerializerBundle.git
        target=bundles/JMS/SerializerBundle

Run the vendors script:

```bash
$ php bin/vendors install
```

**Using Git submodule**

```bash
$ git submodule add git://github.com/FriendsOfSymfony/FOSRestBundle.git vendor/bundles/FOS/RestBundle
```

### Add the FOS namespace to your autoloader

```php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    'FOS' => __DIR__.'/../vendor/bundles',
    // your other namespaces
));
```

### Add this bundle to your application's kernel

```php
<?php
// app/AppKernel.php

public function registerBundles()
{
    return array(
        // ...
        new FOS\RestBundle\FOSRestBundle(),
        // ...
    );
}
```

Examples
========

The LiipHelloBundle provides several examples for the RestBundle:
https://github.com/liip/LiipHelloBundle

There is also a fork of the Symfony2 Standard Edition that is configured to show the LiipHelloBundle examples:
https://github.com/lsmith77/symfony-standard/tree/techtalk

View support
------------

### Introduction

The view layer makes it possible to write format agnostic controllers, by
placing a layer between the Controller and the generation of the final output
via the templating or JMSSerializerBundle.

In your controller action you will then need to create a ``View`` instance that is then
passed to the ``fos_rest.view_handler`` service for processing. The ``View`` is somewhat
modeled after the ``Response`` class, but as just stated it simply works as a container
for all the data/configuration for the ``ViewHandler`` class for this particular action.
So the ``View`` instance must always be processed by a ``ViewHandler`` (see the below
section on the "view response listener" for how to get this processing applied automatically)

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\View\View;

class UsersController extends Controller
{
    public function getUsersAction()
    {
        $view = View::create()
          ->setStatusCode(200);

        ...

        return $this->get('fos_rest.view_handler')->handle($view);
    }
}
```

In the above example, ``View::create`` is a simple, convenient method to allow
for a fluent interface. It is equivalent to instantiating a View by calling its
constructor.

There are also two specialized ``View`` classes for handling directs, one for redirecting
to an URL called ``RedirectView`` and one to redirect to a route called ``RouteRedirectView``.
Note that whether these classes actually cause a redirect or not is determined by the
``force_redirects`` configuration option, which is only enabled for ``html`` by default (see below).

See the following example code for more details:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/HelloController.php

### Configuration

The ``formats`` and ``templating_formats`` settings determine which formats are respectively supported by
the serializer and by the template layer. In other words any format listed in ``templating_formats``
will require a template for rendering using the ``templating`` service, while any format
listed in ``formats`` will use JMSSerializerBundle for rendering.  For both settings a value of
``false`` means that the given format is disabled.

When using ``RouteRedirectView::create()`` the default behavior of forcing a redirect to the
route for html is enabled, but needs to be enabled for other formats if needed.

Finally the HTTP response status code for failed validation defaults to ``400``. Note when
changing the default you can use name constants of ``FOS\RestBundle\Response\Codes`` class or
an integer status code.

You can also set the default templating engine to something different than the default of ``twig``:

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

While many things should be possible via the JMSSerializerBundle in some cases it might
not be enough. For example you might need some custom logic to be executed in the
``ViewHandler``. For these cases one might want to register a custom handler for a
specific format. The custom handler can either be registered by defining a custom service,
via a compiler pass or it can even be registered from inside the controller action.

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

There is another example in ``Resources\docs\examples``:
https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/docs/examples/RssHandler.php

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

Listener support
----------------

All listeners except the ``mime_type`` one are enabled by default.
You can disable one or more of these listeners.
For example, below you can see how to disable all listeners:

```yaml
# app/config/config.yml
fos_rest:
    body_listener: false
    format_listener: false
    view:
        view_response_listener: false
```

### View Response listener

The view response listener makes it possible to simply return a ``View`` instance from action
controllers. The final output will then automatically be processed via the listener by the
``fos_rest.view_handler`` service.

This requires adding the SensioFrameworkExtraBundle to your vendors:

http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html

Now inside a controller its possible to simply return a ``View`` instance.

```php
<?php

use FOS\RestBundle\View\View;

class UsersController
{
    public function getUsersAction()
    {
        $view = View::create();

        ...

        $view->setData($data);
        return $view;
    }
}
```

As this feature is heavily based on the SensioFrameworkBundle, the example can further be
simplified by using the various annotations supported by that bundle. There is also one
additional annotation called ``@View()`` which extends from the ``@Template()`` annotation.

The ``@View()`` and ``@Template()`` annotations behave essentially the same with a minor
difference. When ``view_response_listener`` is set to ``true`` instead of the default ``force``
and ``@View()`` is not used, then rendering will be delegated to SensioFrameworkBundle.

Note that it is necessary to disable view annotations in SensioFrameworkBundle so that
FOSRestBundle can take over the handling.

```yaml
# app/config/config.yml
fos_rest:
    view:
        view_response_listener: force

sensio_framework_extra:
    view:    { annotations: false }
    router:  { annotations: true }
```

```php
<?php

use FOS\RestBundle\Controller\Annotations\View;

class UsersController
{
    /**
     * @View()
     */
    public function getUsersAction()
    {
        ...

        return $data;
    }
}
```

See the following example code for more details:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/ExtraController.php

### Body listener

The Request body decoding listener makes it possible to decode the contents of a request
in order to populate the "request" parameter bag of the Request. This for example allows
to receive data that normally would be sent via POST as ``application/x-www-form-urlencode``
in a different format (for example application/json) in a PUT.

You can add a decoder for a custom format. You can also replace the default decoder services
provided by the bundle for the ``json`` and ``xml`` formats.
Below you can see how to override the decoder for the json format (the xml
decoder is explicitely kept to its default service):

```yaml
# app/config/config.yml
fos_rest:
    body_listener:
        decoders:
            json: acme.decoder.json
            xml: fos_rest.decoder.xml
```

Your custom decoder service must use a class that implements the
``FOS\RestBundle\Decoder\DecoderInterface``.

### Format listener

The Request format listener attempts to determine the best format for the request based on
the Request's Accept-Header and the format priority configuration. This way it becomes
possible to leverage Accept-Headers to determine the request format, rather than a file
extension (like foo.json).

The ``default_priorities`` define the order of formats as the application prefers.
The algorithm iteratively examines the provided Accept header first looking at all the
options with the highest ``q``. The first priority that matches is returned. If none match
the next lowest set of Accept headers with equal ``q`` is examined and so on until there
are no more Accept headers to check. In this case ``fallback_format`` is used.

Note that if ``_format`` is matched inside the route, then a virtual Accept header setting is
added with a ``q`` setting one lower than the lowest Accept header, meaning that format is
checked for a match in the priorities last. If ``prefer_extension`` is set to ``true`` then
the virtual Accept header will be one higher than the highest ``q`` causing the extension
to be checked first.

Note that setting ``default_priorities`` to a non empty array enables Accept header negotiations,
while adding '*/*' to the priorities will effectively cause any priority to match.

```yaml
# app/config/config.yml
fos_rest:
    format_listener:
        default_priorities: ['json', html, '*/*']
        fallback_format: json
        prefer_extension: true
```

For example using the above configuration and the following Accept header:
```
text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json
```

And the following route:

```yaml
hello:
    pattern:  /foo.{_format}
    defaults: { _controller: foo.controller:indexAction, _format: ~ }
```

When calling:

* ``/foo`` will lead to setting the request format to ``json``
* ``/foo.html`` will lead to setting the request format to ``html``

Note that the format needs to either be supported by the ``Request`` class natively or
it needs to be added as documented here:
http://symfony.com/doc/current/cookbook/request/mime_type.html

### Mime type listener

This listener allows registering additional mime types in the ``Request`` class.
It works similar to the following cookbook entry:
http://symfony.com/doc/current/cookbook/request/mime_type.html


```yaml
# app/config/config.yml
fos_rest:
    view:
        mime_types: ['jsonp': ['application/javascript', 'application/javascript+jsonp']]
```


ExceptionController support
---------------------------

Using this custom ExceptionController it is possible to leverage the View layer
when building responses for uncaught Exceptions.

To enable the RestBundle view-layer-aware ExceptionController update the twig
section of your config as follows:

```yaml
# app/config/config.yml
twig:
    exception_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'
```

To map Exception classes to HTTP response status codes an “exception map” may be configured,
where the keys match a fully qualified class name and the values are either an integer HTTP response
status code or a string matching a class constant of the ``FOS\RestBundle\Response\Codes`` class:

```yaml
# app/config/config.yml
fos_rest:
    exception:
        codes:
            'Symfony\Component\Routing\Exception\ResourceNotFoundException': 404
            'Doctrine\ORM\OptimisticLockException': HTTP_CONFLICT
        messages:
            'Acme\HelloBundle\Exception\MyExceptionWithASafeMessage': true
```

If you want to display the message from the exception in the content of the response, add the 
exception to the messages map as well. If not only the status code will be returned.

If you know what status code you want to return you do not have to add a mapping, you can do
this in your controller:

```php
<?php
class UsersController extends Controller
{
    public function postUserCommentsAction($slug)
    {
        if (!$this->validate($slug)) {
            throw new HttpException(400, "New comment is not valid.");
        }
    }
}
```

See the following example configuration for more details:
https://github.com/lsmith77/symfony-standard/blob/techtalk/app/config/config.yml

Routing
=======

The RestBundle provides custom route loaders to help in defining REST friendly routes
as well as reducing the manual work of configuring routes and the given requirements
(like making sure that only GET may be used in certain routes etc.).

You may specify a ``default_format`` that the routing loader will use for the ``_format``
parameter if none is specified.

```yaml
# app/config/config.yml
fos_rest:
    routing_loader:
        default_format: json
```

Many of the features explained below are used in the following example code:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/RestController.php

Single RESTful controller routes
--------------------------------

```yaml
# app/config/routing.yml
users:
    type:     rest
    resource: Acme\HelloBundle\Controller\UsersController
```

This will tell Symfony2 to automatically generate proper REST routes from your ``UsersController`` action names.
Notice ``type: rest`` option. It's required so that the RestBundle can find which routes are supported.

## Define resource actions

```php
<?php
class UsersController extends Controller
{
    public function getUsersAction()
    {} // "get_users"    [GET] /users

    public function newUsersAction()
    {} // "new_users"    [GET] /users/new

    public function postUsersAction()
    {} // "post_users"   [POST] /users

    public function patchUsersAction()
    {} // "patch_users"   [PATCH] /users

    public function getUserAction($slug)
    {} // "get_user"     [GET] /users/{slug}

    public function editUserAction($slug)
    {} // "edit_user"    [GET] /users/{slug}/edit

    public function putUserAction($slug)
    {} // "put_user"     [PUT] /users/{slug}

    public function patchUserAction($slug)
    {} // "patch_user"   [PATCH] /users/{slug}

    public function lockUserAction($slug)
    {} // "lock_user"    [PUT] /users/{slug}/lock

    public function banUserAction($slug, $id)
    {} // "ban_user"     [PUT] /users/{slug}/ban

    public function removeUserAction($slug)
    {} // "remove_user"  [GET] /users/{slug}/remove

    public function deleteUserAction($slug)
    {} // "delete_user"  [DELETE] /users/{slug}

    public function getUserCommentsAction($slug)
    {} // "get_user_comments"    [GET] /users/{slug}/comments

    public function newUserCommentsAction($slug)
    {} // "new_user_comments"    [GET] /users/{slug}/comments/new

    public function postUserCommentsAction($slug)
    {} // "post_user_comments"   [POST] /users/{slug}/comments

    public function getUserCommentAction($slug, $id)
    {} // "get_user_comment"     [GET] /users/{slug}/comments/{id}

    public function editUserCommentAction($slug, $id)
    {} // "edit_user_comment"    [GET] /users/{slug}/comments/{id}/edit

    public function putUserCommentAction($slug, $id)
    {} // "put_user_comment"     [PUT] /users/{slug}/comments/{id}

    public function voteUserCommentAction($slug, $id)
    {} // "vote_user_comment"    [PUT] /users/{slug}/comments/{id}/vote

    public function removeUserCommentAction($slug, $id)
    {} // "remove_user_comment"  [GET] /users/{slug}/comments/{id}/remove

    public function deleteUserCommentAction($slug, $id)
    {} // "delete_user_comment"  [DELETE] /users/{slug}/comments/{id}
}
```

That's all. All your resource (``UsersController``) actions will get mapped to the proper routes
as shown in the comments in the above example. Here are a few things to note:

### REST Actions

There are 5 actions that have special meaning in regards to REST and have the following behavior:

* **get** - this action accepts *GET* requests to the url */resources* and returns all resources for this type. Shown as
``UsersController::getUsersAction()`` above. This action also accepts *GET* requests to the url */resources/{id}* and
returns a single resource for this type. Shown as ``UsersController::getUserAction()`` above.
* **post** - this action accepts *POST* requests to the url */resources* and creates a new resource of this type. Shown
as ``UsersController::postUsersAction()`` above.
* **put** - this action accepts *PUT* requests to the url */resources/{id}* and updates a single resource for this type.
Shown as ``UsersController::putUserAction()`` above.
* **delete** - this action accepts *DELETE* requests to the url */resources/{id}* and deltes a single resource for this
type. Shown as ``UsersController::deleteUserAction()`` above.
* **patch** - this action accepts *PATCH* requests to the url */resources* and is supposed to partially modify collection
of resources (e.g. apply batch modifications to subset of resources). Shown as ``UsersController::patchUsersAction()`` above.
This action also accepts *PATCH* requests to the url */resources/{id}* and is supposed to partially modify the resource. 
Shown as ``UsersController::patchUserAction()`` above.

### Conventional Actions

HATEOAS, or Hypermedia as the Engine of Application State, is an aspect of REST which allows clients to interact with the
REST service with hypertext - most commonly through an HTML page. There are 3 Conventional Action routings that are
supported by this bundle:

* **new** - A hypermedia representation that acts as the engine to *POST*. Typically this is a form that allows the client
to *POST* a new resource. Shown as ``UsersController::newUsersAction()`` above.
* **edit** - A hypermedia representation that acts as the engine to *PUT*. Typically this is a form that allows the client
to *PUT*, or update, an existing resource. Shown as ``UsersController::editUserAction()`` above.
* **remove** - A hypermedia representation that acts as the engine to *DELETE*. Typically this is a form that allows the
client to *DELETE* an existing resource. Commonly a confirmation form. Shown as ``UsersController::removeUserAction()`` above.

### Custom PUT Actions

All actions that do not match the ones listed in the sections above will register as a *PUT* action. In the controller
shown above, these actions are ``UsersController::lockUserAction()`` and ``UsersController::banUserAction()``. You could
just as easily create a method called ``UsersController::promoteUserAction()`` which would take a *PUT* request to the url
*/users/{slug}/promote*. This allows for easy updating of aspects of a resource, without having to deal with the
resource as a whole at the standard *PUT* endpoint.

### Sub-Resource Actions

Of course it's possible and common to have sub or child resources. They are easily defined within the same controller by
following the naming convention ``ResourceController::actionResourceSubResource()`` - as seen in the example above with
``UsersController::getUserCommentsAction()``. This is a good strategy to follow when the child resource needs the parent
resource's ID in order to look up itself. 

Relational RESTful controllers routes
-------------------------------------

Sometimes it's better to place subresource actions in their own controller, especially when
you have more than 2 subresource actions.

## Resource collection

In this case, you must first specify resource relations in special rest YML or XML collection:

```yaml
# src/Acme/HelloBundle/Resources/config/users_routes.yml
users:
    type:     rest
    resource: "@AcmeHello\Controller\UsersController"

comments:
    type:     rest
    parent:   users
    resource: "@AcmeHello\Controller\CommentsController"
```

Notice ``parent: users`` option in the second case. This option specifies that the comments resource
is child of the users resource. In this case, your ``UsersController`` MUST always have a single
resource ``get...`` action:

```php
<?php
class UsersController extends Controller
{
    public function getUserAction($slug)
    {} // "get_user"   [GET] /users/{slug}

    ...
}
```

It's used to determine the parent collection name. Controller name itself not used in routes
auto-generation process and can be any name you like.

## Define child resource controller

``CommentsController`` actions now will looks like:

```php
<?php
class CommentsController extends Controller
{
    public function voteCommentAction($slug, $id)
    {} // "vote_user_comment"   [PUT] /users/{slug}/comments/{id}/vote

    public function getCommentsAction($slug)
    {} // "get_user_comments"   [GET] /users/{slug}/comments

    public function getCommentAction($slug, $id)
    {} // "get_user_comment"    [GET] /users/{slug}/comments/{id}

    public function deleteCommentAction($slug, $id)
    {} // "delete_user_comment" [DELETE] /users/{slug}/comments/{id}

    public function newCommentsAction($slug)
    {} // "new_user_comments"   [GET] /users/{slug}/comments/new

    public function editCommentAction($slug, $id)
    {} // "edit_user_comment"   [GET] /users/{slug}/comments/{id}/edit

    public function removeCommentAction($slug, $id)
    {} // "remove_user_comment" [GET] /users/{slug}/comments/{id}/remove
}
```

Notice, we got rid of the ``User`` part in action names. That is because the RestBundle routing
already knows, that ``CommentsController::...`` is child resources of ``UsersController::getUser()``
resource.

## Include resource collections in application routing

Last step is mapping of your collection routes into the application ``routing.yml``:

```yaml
# app/config/routing.yml
users:
    type:     rest
    resource: "@AcmeHello/Resources/config/users_routes.yml"
```

That's all. Note that it's important to use the ``type: rest`` param when including your application's
routing file. Without it, rest routes will still work but resource collections will fail. If you get an
exception that contains ...routing loader does not support given key: "parent"... then you are most likely missing
the ``type: rest`` param in your application level routes include.

## Routes naming

RestBundle uses REST paths to generate route name. This means, that URL:

    [PUT] /users/{slug}/comments/{id}/vote

will become the route with the name ``vote_user_comment``.

For further examples, see comments of controllers in the code above.

### Naming collisions

Sometimes, routes auto-naming will lead to route names collisions, so RestBundle route
collections provides a ``name_prefix`` (``name-prefix`` for xml and ``@NamePrefix`` for
annotations) parameter:

```yaml
# src/Acme/HelloBundle/Resources/config/users_routes.yml
comments:
    type:         rest
    resource:     "@AcmeHello\Controller\CommentsController"
    name_prefix:  api_
```

With this configuration, route name would become:

    api_vote_user_comment

Say NO to name collisions!

Full default configuration
==========================

```yaml
fos_rest:
    routing_loader:
        default_format: null
    view:
        default_engine: twig
        force_redirects:
            html: true
        formats:
            json: true
            xml: true
        templating_formats:
            html: true
        view_response_listener: 'force'
        failed_validation: HTTP_BAD_REQUEST
    exception:
        codes: ~
        messages: ~
    body_listener:
        decoders:
            json: fos_rest.decoder.json
            xml: fos_rest.decoder.xml
    format_listener:
        default_priorities: [html, '*/*']
        fallback_format: html
        prefer_extension: true
    service:
        router: router
        templating: templating
        serializer: serializer
        view_handler: fos_rest.view_handler.default
```


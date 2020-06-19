Step 2: The View Layer
======================

Why View Layer
--------------

The View Layer resides between the Controller and the Serializer:

``Controller --> View Layer --> Serializer --> [final output]``

This structure allows a developer to create a ``format`` (html, json, xml, etc) 
agnostic Controller:

1. The Controller prepares required data / configuration and injects it into the View object
2. The Controller returns the View object
3. A special View Listener 'hears' the View being returned and passes the View
to the View Handler for the further processing (in case of FOSRestBundle,
this is the ``fos_rest.view_handler`` service)

The Controller in this sctructure is not obliged to return data in any specific format:
the needed format is set up later, during the processing of the View.

The data injected from the Controller into the View can be of any type, though 
the best practice is to use an object graph.

View Layer in FOSRestBundle
---------------------------

With the FOSRestBundle, for the serialization purposes, you can use one of these:

* standard `Symfony Serializer Component`_
* more sophisticated Johannes Schmitt's `serializer`_, integrated via the `JMSSerializerBundle`_.

FOSRestBundle ships with a controller extending the default Symfony controller,
which adds several convenience methods:

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Controller\AbstractFOSRestController;

    class UsersController extends AbstractFOSRestController
    {
        public function getUsersAction()
        {
            $data = ...; // get data, in this case list of users.
            $view = $this->view($data, 200);

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

.. versionadded:: 2.0
    The ``ControllerTrait`` trait was added in 2.0.

If you prefer to avoid injecting a container into the controller, you should
do the following:

* use the ``ControllerTrait`` for you controller
* inject a ``ViewHandlerInterface`` instance via setter injection ``setViewHandler``

If you rely on the ``ViewResponseListener`` in combination with SensioFrameworkExtraBundle,
you can just return the View instance and omit the call ``$this->handleView($view)``.
Please refer to `Chapter 3`_ for more details on the View Response Listener.

View methods
------------

Redirection

* ``View::createRedirect`` - Redirect to an URL called ``RedirectView``.
* ``View::createRouteRedirect`` - Redirect to a route.

Configuration

* ``setData($data)`` - Set the object graph or list of objects to serialize.
* ``setHeader($name, $value)`` - Set a header to put on the HTTP response.
* ``setHeaders(array $headers)`` - Set multiple headers to put on the HTTP response.
* ``setStatusCode($code)`` - Set the HTTP status code.
* ``getContext()`` - The serialization context in use.
* ``setFormat($format)`` - The format the response is supposed to be rendered in.
  Can be autodetected using HTTP semantics.
* ``setLocation($location)`` - The location to redirect to with a response.
* ``setRoute($route)`` - The route to redirect to with a response.
* ``setRouteParameters($parameters)`` - Set the parameters for the route.
* ``setResponse(Response $response)`` - The response instance that is populated
  by the ``ViewHandler``.
  
There are also several more methods available, please refer to the code.

Forms and Views
---------------

Symfony Forms have special handling inside the view layer. Whenever you:

- Return a Form from the controller,
- Set the form as only data of the view,
- Return an array with a ``'form'`` key, containing a form, or
- Return a form with validation errors.

Then:

- If the form is bound and no status code is set explicitly, an invalid form
  leads to a "validation failed" response.
- An invalid form will be wrapped in an exception.

A response example of an invalid form:

.. code-block:: javascript

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

If you don't like the default exception structure, you can provide your own
normalizers.

You can look at `FOSRestBundle normalizers`_ for examples.

Data Transformation
-------------------

As we have seen in the section before, the FOSRestBundle relies on the `Form Component`_ to
handle submission of view data. In fact, the `Form Builder`_ basically
defines the structure of the expected view data which shall be used for further
processing - which most of the time relates to a PUT or POST request. This
brings a lot of flexibility and allows to exactly define the structure of data
to be received by the API.

Most of the time the requirements regarding a PUT/POST request are, in
terms of data structure, fairly simple. The payload within a PUT or POST request
oftentimes will have the exact same structure as received by a previous GET
request, but only with modified value fields. Thus, the fields to be defined
within the form builder process will be the same as the fields marked to be
serialized within an entity.

However, there is a common use case where straightforward updating of data,
received by a serialized object (GET request), will not work out of the box using
the given implementation of the form component: Simple assignment of a reference
using an object.

Let's take an entity ``Task`` that holds a reference to a ``Person`` as
an example. The serialized Task object will looks as follows:

.. code-block:: json

    {"task_form":{"name":"Task1", "person":{"id":1, "name":"Fabien"}}}

In a traditional Symfony application we simply define the property of the
related class and it would perfectly assign the person to our task - in this
case based on the ``id``:

.. code-block:: php

    $builder
        ->add('name', 'text')
        ...
        ->add('person', 'entity', array(
            'class' => 'Acme\DemoBundle\Entity\Person',
            'property' => 'id'
        ))

Unfortunately, this form builder does not accept our serialized object as it is
- even though it contains the necessary id. In fact, the object would have to
contain the id directly assigned to the person field to be accepted by the
form validation process:

.. code-block:: json

    {"task_form":{"name":"Task1", "person":1}}

This is somewhat useless since we not only want to display the name of the
person, but also do not want to do some client side trick to extract the id
before updating the data. Instead, we rather update the data the same way
as we received it in our GET request and thus, extend the form builder with a
data transformer. Fortunately, the FOSRestBundle comes with an
``EntityToIdObjectTransformer``, which can be applied to any form builder:

.. code-block:: php

    $personTransformer = new EntityToIdObjectTransformer($this->om, "AcmeDemoBundle:Person");
    $builder
        ->add('name', 'text')
        ...
        ->add($builder->create('person', 'text')->addModelTransformer($personTransformer))

This way, the data structure remains untouched and the person can be assigned to
the task without any client modifications.

Configuration
-------------

The ``formats`` setting determines which formats are supported by the serializer.
In other words any format listed in ``formats`` will use the serializer for rendering.
A value of ``false`` means that the given format is disabled.

When using ``RouteRedirectView::create()`` the default behavior of forcing a
redirect to the route when HTML is enabled, but this needs to be enabled for other
formats as needed.

Finally the HTTP response status code for failed validation defaults to
``400``. Note when changing the default you can use name constants of
``Symfony\Component\HttpFoundation\Response`` class or an integer status code.

Custom handler
--------------

While many things should be possible via the serializer, in some cases
it might not be enough. For example you might need some custom logic to be
executed in the ``ViewHandler``. For these cases one might want to register a
custom handler for a specific format. The custom handler can either be
registered by defining a custom service, via a compiler pass, or it can be
registered from inside the controller action.

The callable will receive 3 parameters:

* the instance of the ``ViewHandler``
* the instance of the ``View``
* the instance of the ``Request``

Note there are several public methods on the ``ViewHandler`` which can be helpful:

* ``createResponse()``
* ``createRedirectResponse()``

There is `an example of how to register a custom handler`_ (for an RSS feed) in ``Resources\doc\examples``.

JSONP custom handler
~~~~~~~~~~~~~~~~~~~~

To enable the common use case of creating JSONP responses, this Bundle provides an
easy solution to handle a custom handler for this use case. Enabling this setting
also automatically uses the mime type listener (see the next chapter) to register
a mime type for JSONP.

Simply add the following to your configuration

.. code-block:: yaml

    fos_rest:
        view:
            jsonp_handler: ~

It is also possible to customize both the name of the GET parameter with the
callback, as well as the filter pattern that validates if the provided callback
is valid or not.

.. code-block:: yaml

    fos_rest:
        view:
            jsonp_handler:
               callback_param: mycallback

Finally the filter can also be disabled by setting it to false.

.. code-block:: yaml

    fos_rest:
        view:
            jsonp_handler:
                callback_param: false

When working with JSONP, be aware of `CVE-2014-4671`_ (full explanation can be
found here: `Abusing JSONP with Rosetta Flash`_). You SHOULD use `NelmioSecurityBundle`_
and `disable the content type sniffing for script resources`_.

CSRF validation
~~~~~~~~~~~~~~~

When building a single application that should handle forms both via HTML forms
as well as via a REST API, one runs into a problem with CSRF token validation.
In most cases, it is necessary to enable them for HTML forms, but it makes no
sense to use them for a REST API. For this reason there is a form extension to
disable CSRF validation for users with a specific role. This of course requires
that REST API users authenticate themselves and get a special role assigned.

.. code-block:: yaml

    fos_rest:
        disable_csrf_role: ROLE_API

That was it!

.. _`an example of how to register a custom handler`: https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/examples/RssHandler.php
.. _`Chapter 3`: https://symfony.com/doc/master/bundles/FOSRestBundle/3-listener-support.html
.. _`FOSRestBundle normalizers`: https://github.com/FriendsOfSymfony/FOSRestBundle/tree/master/Serializer/Normalizer
.. _`Form Component`: https://symfony.com/doc/current/components/form/introduction.html
.. _`Form Builder`: https://symfony.com/doc/current/book/forms.html#building-the-form
.. _`Symfony Serializer Component`: http://symfony.com/doc/current/components/serializer.html
.. _`serializer`: https://github.com/schmittjoh/serializer
.. _`JMSSerializerBundle`: https://github.com/schmittjoh/JMSSerializerBundle
.. _`CVE-2014-4671`: http://web.nvd.nist.gov/view/vuln/detail?vulnId=CVE-2014-4671
.. _`Abusing JSONP with Rosetta Flash`: http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/
.. _`NelmioSecurityBundle`: https://github.com/nelmio/NelmioSecurityBundle
.. _`disable the content type sniffing for script resources`: https://github.com/nelmio/NelmioSecurityBundle#content-type-sniffing

Step 3: Listener support
========================

`Listeners`_ are a way to hook into the request handling. This Bundle provides
various events from decoding the request content in the request (body listener),
determining the correct response format (format listener), reading parameters
from the request (parameter fetcher listener), to formatting the response either
with a template engine like twig or to e.g. xml or json using a serializer (view
response listener) as well as automatically setting the accepted HTTP methods
in the response (accept listener).

With this in mind we now turn to explain each one of them.

All listeners except the ``mime_type`` listener are disabled by default. You
can enable one or more of these listeners. For example, below you can see how
to enable a few additional listeners:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        param_fetcher_listener: true
        body_listener: true
        format_listener:
            enabled: true
            rules:
                - { path: '^/', priorities: ['json', 'xml'], fallback_format: 'html' }
        versioning: true
        view:
            view_response_listener: 'force'

It is possible to replace the service used for each of the listener if needed.
In this case, the Bundle listener will still be configured, however it will
not be registered in the kernel. The custom service listener will however not
be registered in the kernel, so it is up to the user to register it for the
appropriate event:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_listener:
            service: my_body_listener

    my_body_listener:
        class: Acme\BodyListener
        tags:
            - { name: kernel.event_listener, event: kernel.request, method: onKernelRequest, priority: 10 }
        arguments: ['@fos_rest.decoder_provider', '%fos_rest.throw_exception_on_unsupported_content_type%']
        calls:
            - [setDefaultFormat, ['%fos_rest.body_default_format%']]


View Response Listener
----------------------

The view response listener makes it possible to simply return a ``View``
instance from action controllers. The final output will then automatically be
processed via the listener by the ``fos_rest.view_handler`` service.

This requires adding the `SensioFrameworkExtraBundle`_ to your vendors.

For details see :doc:`View Response Listener <view_response_listener>`.

Body Listener
-------------

The Request body listener makes it possible to decode the contents of a request
in order to populate the "request" parameter bag of the Request. This, for
example, allows to receive data that normally would be sent via POST as
``application/x-www-form-urlencode`` in a different format (for example
application/json) in a PUT.

For details see :doc:`Body Listener <body_listener>`.

Request Body Converter Listener
-------------------------------

`ParamConverters`_ are a way to populate objects and inject them as controller
method arguments. The Request body converter makes it possible to deserialize
the request body into an object.

This converter requires that you have installed `SensioFrameworkExtraBundle`_
and have the converters enabled.

For details see :doc:`Request Body Converter Listener <request_body_converter_listener>`.

Format Listener
---------------

The Request format listener attempts to determine the best format for the
request based on the HTTP Accept header and the format priority
configuration. This way it becomes possible to leverage Accept-Headers to
determine the request format, rather than a file extension (like foo.json).

For details see :doc:`Format Listener <format_listener>`.

Versioning
----------

This listener attemps to determine the current api version from different parameters of the ``Request``:
    - the uri ``/{version}/users``
    - a query parameter ``/users?version=v1``
    - an ``Accept`` header ``Accept: appication/json; version=1.0``
    - a custom header ``X-Accept-Version: v1``

For details see :doc:`Versioning <versioning>`.

Mime Type Listener
------------------

This listener allows registering additional mime types in the ``Request``
class. It works similar to the `mime type listener`_ available in Symfony
since 2.5.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        view:
            mime_types: {'jsonp': ['application/javascript+jsonp']}

Param Fetcher Listener
----------------------

The param fetcher listener simply sets the ParamFetcher instance as a request attribute
configured for the matched controller so that the user does not need to do this manually.

For details see :doc:`Param Fetcher Listener <param_fetcher_listener>`.

Allowed Http Methods Listener
-----------------------------

This listener adds the ``Allow`` HTTP header to each request appending all
allowed methods for a given resource.

Let's say we have the following routes:

.. code-block:: text

    api_get_users
    api_post_users
    api_get_user

A ``GET`` request to ``api_get_users`` will respond with:

.. code-block:: text

    HTTP/1.0 200 OK
    Date: Sat, 16 Jun 2012 15:17:22 GMT
    Server: Apache/2.2.22 (Ubuntu)
    Allow: GET, POST

You need to enable this listener as follows, as it is disabled by default:

.. code-block:: yaml

    fos_rest:
        allowed_methods_listener: true

Security Exception Listener
---------------------------

By default it is the responsibility of firewall access points to deal with
AccessDeniedExceptions. For example the ``form`` entry point will redirect to
the login page. However, for a RESTful application proper response HTTP status
codes should be provided. This listener is triggered before the normal exception
listener and firewall entry points and forces returning either a 403 or 401
status code for any of the formats configured.

It will return 401 for
``Symfony\Component\Security\Core\Exception\AuthenticationException`` or 403 for
``Symfony\Component\Security\Core\Exception\AccessDeniedException``.

As a 401-response requires an authentication-challenge, you can set one using
the configuration ``unauthorized_challenge`` or leave it blank if you don't want
to send a challenge in the ``WWW-Authenticate`` header to the client.

If you want to use an advanced value in this header, it's worth looking at this:
`Test Cases for HTTP Test Cases for the HTTP WWW-Authenticate header field`_.

You need to enable this listener as follows, as it is disabled by default:

.. code-block:: yaml

    fos_rest:
        unauthorized_challenge: "Basic realm=\"Restricted Area\""
        access_denied_listener:
            # all requests using the 'json' format will return a 403 on an access denied violation
            json: true

It is also recommended to enable the exception controller described in the next chapter.

Zone Listener
=============

As you can see, FOSRestBundle provides multiple event listeners to enable REST-related features.
By default, these listeners will be registered to all requests and may conflict with other parts of your application.

Using the ``zone`` configuration, you can specify where the event listeners will be enabled. The zone configuration
allows to configure multiple zones in which the above listeners will be active. If no zone is configured, it means
that the above listeners will not be limited. If at least one zone is configured then the above listeners will
be skipped for all requests that do not match at least one zone. For a single zone config entry can contain matching
rules on the request ``path``, ``host``, ``methods`` and ``ip``.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        zone:
            - { path: ^/api/* }

Priorities
----------

==========================  =====================  ========
Listener                    Event                  Priority
==========================  =====================  ========
``ZoneMatcherListener``     ``kernel.request``     248
``MimeTypeListener``        ``kernel.request``     200
``FormatListener``          ``kernel.request``     34
``VersionListener``         ``kernel.request``     33
``BodyListener``            ``kernel.request``     10
``ParamFetcherListener``    ``kernel.controller``  5
``ViewResponseListener``    ``kernel.controller``  -10
``ViewResponseListener``    ``kernel.view``        100
``AllowedMethodsListener``  ``kernel.response``    0
==========================  =====================  ========

That was it!

.. _`Listeners`: http://symfony.com/doc/master/cookbook/service_container/event_listener.html
.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`ParamConverters`: http://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`mime type listener`: http://symfony.com/doc/current/cookbook/request/mime_type.html
.. _`Test Cases for HTTP Test Cases for the HTTP WWW-Authenticate header field`: http://greenbytes.de/tech/tc/httpauth/

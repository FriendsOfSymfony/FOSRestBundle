Format Listener
===============

The Request format listener attempts to determine the best format for the
request based on the Request's Accept-Header and the format priority
configuration. This way it becomes possible to leverage Accept-Headers to
determine the request format, rather than a file extension (like foo.json).

The ``priorities`` define the order of media types as the application
prefers. Note that if a format is provided instead of a media type, the
format is converted into a list of media types matching the format.
The algorithm iteratively examines the provided Accept header first
looking at all the options with the highest ``q``. The first priority that
matches is returned. If none match the next lowest set of Accept headers with
equal ``q`` is examined and so on until there are no more Accept headers to
check. In this case ``fallback_format`` is used.

Note that if ``_format`` is matched inside the route, then a virtual Accept
header setting is added with a ``q`` setting one lower than the lowest Accept
header, meaning that format is checked for a match in the priorities last. If
``prefer_extension`` is set to ``true`` then the virtual Accept header will be
one higher than the highest ``q`` causing the extension to be checked first.
Setting ``priorities`` to a non-empty array enables Accept header negotiations.

.. code-block:: yaml

    fos_rest:
        format_listener:
            enabled: true
            rules:
                # setting fallback_format to json means that instead of considering the next rule in case of a priority mismatch, json will be used
                - { path: '^/', host: 'api.%domain%', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: false }
                # setting fallback_format to false means that instead of considering the next rule in case of a priority mismatch, a 406 will be caused
                - { path: '^/image', priorities: ['jpeg', 'gif'], fallback_format: false, prefer_extension: true }
                # setting fallback_format to null means that in case of a priority mismatch the next rule will be considered
                - { path: '^/admin', methods: ['GET', 'POST'], priorities: ['xml', 'html'], fallback_format: ~, prefer_extension: false }
                # you can specifically target the exception controller
                - { path: '^/api', priorities: ['xml', 'json'], fallback_format: xml, attributes: { _controller: FOS\RestBundle\Controller\ExceptionController }, prefer_extension: false }
                # setting a priority to */* basically means any format will be matched
                - { path: '^/', priorities: ['text/html', '*/*'], fallback_format: html, prefer_extension: true }

For example using the above configuration and the following Accept header:

.. code-block:: text

    text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json

And the following route:

.. code-block:: yaml

    hello:
        path:  /foo.{_format}
        defaults: { _controller: foo.controller:indexAction, _format: ~ }

When calling:

* ``/foo.json`` will lead to setting the request format to ``json``
* ``/foo`` will lead to setting the request format to ``html``

Furthermore the listener sets a ``media_type`` attribute on the request in
case the listener is configured with a ``MediaTypeNegotiatorInterface`` instance,
which is the case by default, with the matched media type.

.. code-block:: php

    // f.e. text/html or application/vnd.custom_something+json etc.
    $mediaType = $request->attributes->get('media_type');

The ``priorities`` should be configured carefully, especially when the
controller actions for specific routes only handle necessary security checks
for specific formats. In such cases it might make sense to hard code the format
in the controller action.

.. code-block:: php

    public function getAction(Request $request)
    {
        $view = new View();
        // hard code the output format of the controller action
        $view->setFormat('html');

        // ...
    }

Note that if you use custom mime types, they need to be added using the :doc:`Mime Type Listener <3-listener-support>`.

Disabling the Format Listener via Rules
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Often when integrating this Bundle with existing applications, it might be
useful to disable the format listener for some routes. In this case it is
possible to define a rule that will stop the format listener from determining a
format by setting ``stop`` to ``true`` as a rule option. Any rule containing
this setting and any rule following will not be considered and the Request
format will remain unchanged.

.. code-block:: yaml

    fos_rest:
        format_listener:
            enabled: true
            rules:
                - { path: '^/api', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: false }
                - { path: '^/', stop: true } # Available for version >= 1.5

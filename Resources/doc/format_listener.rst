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

    # app/config/config.yml
    fos_rest:
        format_listener:
            rules:
                # setting fallback_format to json means that instead of considering the next rule in case of a priority mismatch, json will be used
                - { path: '^/', host: 'api.%domain%', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: false }
                # setting fallback_format to false means that instead of considering the next rule in case of a priority mismatch, a 406 will be caused
                - { path: '^/image', priorities: ['jpeg', 'gif'], fallback_format: false, prefer_extension: true }
                # setting fallback_format to null means that in case of a priority mismatch the next rule will be considered
                - { path: '^/admin', methods: [ 'GET', 'POST'], priorities: [ 'xml', 'html'], fallback_format: ~, prefer_extension: false }
                # setting fallback_format to null, while setting exception_fallback_format to xml, will mean that in case of an exception, xml will be used
                - { path: '^/api', priorities: [ 'xml', 'json'], fallback_format: ~, exception_fallback_format: xml, prefer_extension: false }
                # setting a priority to */* basically means any format will be matched
                - { path: '^/', priorities: [ 'text/html', '*/*'], fallback_format: html, prefer_extension: true }

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

Note that the format needs to either be supported by the ``Request`` class
natively or it needs to be added as documented here or using the
`mime type listener`_ explained in the Symfony documentation.

Disabling the Format Listener via Rules
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Often when integrating this Bundle with existing applications, it might be
useful to disable the format listener for some routes. In this case it is
possible to define a rule that will stop the format listener from determining a
format by setting ``stop`` to ``true`` as a rule option. Any rule containing
this setting and any rule following will not be considered and the Request
format will remain unchanged.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        format_listener:
            rules:
                - { path: '^/api', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: false }
                - { path: '^/', stop: true } # Available for version >= 1.5

.. _media-type-version-extraction:

Media Type Version Extraction
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The format listener can also determine the version of the selected media type
based on a regular expression. The regular expression can be configured as
follows. Setting it to an empty value will disable the behavior entirely.

.. code-block:: yaml

    fos_rest:
        format_listener:
            media_type:
                version_regex:        '/(v|version)=(?P<version>[0-9\.]+)/'

The matched version is set as a Request attribute with the name ``version``,
and when using JMS serializer it is also set as an exclusion strategy
automatically in the ``ViewHandler``.

To make the version mechanism working :

1 - The client must pass the requested version in his header like this :

.. code-block:: yaml

    Accept:application/json;version=1.0

2 - You must have declared the version value in your config, otherwise it won't be catched :

.. code-block:: yaml

    fos_rest:
        view:
            mime_types:
                json: ['application/json', 'application/json;version=1.0', 'application/json;version=1.1']

3 - You should have tagged your entities with version information (@Since, @Until ...)

See `this JMS Serializer article`_ for details about versioning objects.

.. _`this JMS Serializer article`: http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#versioning-objects

That's it, it should work now.

If you have to verify if the version is correctly catched you can use something like :

.. code-block:: php

        if ($this->container->get('fos_rest.version_listener')) {
            print $this->container->get('fos_rest.version_listener')->getVersion();
        }

Note that this version mechanism is configurable by your own by changing the regular expression in the
:ref:`media type version regex configuration <media-type-version-extraction>`.

.. _`mime type listener`: http://symfony.com/doc/current/cookbook/request/mime_type.html

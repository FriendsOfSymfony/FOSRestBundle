API versioning
==============
Why version your API ?
----------------------
If you want to introduce breaking changes in your API, you should think about version your API. Indeed, if you introduce them on your current API, your users won't necessary upgrade their application directly and their applications won't work.

How to version your API ?
-------------------------
There are several ways, none is standard :
    - Use a query parameter ``/users?version=v1``
    - Use a custom mime-type with an ``Accept`` header ``Accept: application/json; version=1.0``
    - Use a custom header ``X-Accept-Version: v1``

The ``FOSRestBundle`` allows you to use several of them at the same time or to choose one of them.

URI API versioning
------------------
If you want to version your api with the uri, you can simply use the symfony router:

.. code-block:: yaml

    # app/config/routing.yml
    my_route:
        # ...
        path: /{version}/foo/route

Note: this will override the ``version`` attribute of the request if you use the ``FOSRestBundle`` versioning.

Configure ``FOSRestBundle`` to use the api versioning
-----------------------------------------------------
You should activate the versioning in your config.yml:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        versioning: true

If you do not want to allow all the methods described above, you should choose which version resolver to enable:

.. code-block:: yaml

    #app/config/config.yml
    fos_rest:
        versioning:
            enabled: true
            resolvers:
                query: true # Query parameter: /users?version=v1
                custom_header: true # X-Accept-Version header
                media_type: # Accept header
                    enabled: true
                    regex: '/(v|version)=(?P<version>[0-9\.]+)/'

You can also choose the guessing order:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        versioning:
            enabled: true
            guessing_order:
                - query
                - custom_header
                - media_type

The matched version is set as a Request attribute with the name ``version``,
and when using JMS serializer it is also set as an exclusion strategy
automatically in the ``ViewHandler``.

If you use the method using the ``Accept`` header, to make the version mechanism working:

1 - The format listener must be enabled

See :doc:`Format Listener <format_listener>`

2 - The client must pass the requested version in his header like this :

.. code-block:: yaml

    Accept:application/json;version=1.0

3 - You must have declared the version value in your config, otherwise it won't be catched :

.. code-block:: yaml

    fos_rest:
        view:
            mime_types:
                json: ['application/json', 'application/json;version=1.0', 'application/json;version=1.1']

Note: If you have to handle huge versions and mime types, you can simplify the configuration with a php script:

.. code-block:: php

    // app/config/fos_rest_mime_types.php
    $versions = array(
        '1.0',
        '1.1',
        '2.0',
    );

    $mimeTypes = array(
        'json' => array(
            'application/json',
        ),
        'yml'  => array(
            'application/yaml',
            'text/yaml',
        ),
    );

    array_walk($mimeTypes, function (&$mimeTypes, $format, $versions) {
        $versionMimeTypes = array();
        foreach ($mimeTypes as $mimeType) {
            foreach ($versions as $version) {
                array_push($versionMimeTypes, sprintf('%s;version=%s', $mimeType, $version));
                array_push($versionMimeTypes, sprintf('%s;v=%s', $mimeType, $version));
            }
        }
        $mimeTypes = array_merge($mimeTypes, $versionMimeTypes);
    }, $versions);

    $container->loadFromExtension('fos_rest', array(
        'view' => array(
            'mime_types' => $mimeTypes,
        ),
    ));

And then, import it from your config.yml file:

.. code-block:: yaml

    imports:
        - { resource: assets_version.php }

If you have to verify if the version is correctly catched you can use something like :

.. code-block:: php

        if ($this->container->get('fos_rest.versioning.listener')) {
            print $this->container->get('fos_rest.versioning.listener')->getVersion();
        }

Use the ``JMSSerializer`` with the API versioning
-------------------------------------------------
You should have tagged your entities with version information (@Since, @Until ...)

See `this JMS Serializer article`_ for details about versioning objects.

.. _`this JMS Serializer article`: http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#versioning-objects

That's it, it should work now.

How to match a specific version in my routing ?
-----------------------------------------------
You can use conditions on your request to check for the version that was determined:

.. code-block:: yaml

    my_route:
        # ...
        condition: "request.attributes.get('version') == 'v2'"

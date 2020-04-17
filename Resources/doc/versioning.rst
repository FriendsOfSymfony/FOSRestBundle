API versioning
==============

Think about versioning your API
-------------------------------

If you introduce changes to your current API, your users might not upgrade their applications right away, so versioning is important to prevent existing applications to break when such changes are made.

How to version your API ?
-------------------------

There are several ways, none is standard:

* Use an uri parameter ``/v1/users``
* Use a query parameter ``/users?version=v1``
* Use a custom mime-type with an ``Accept`` header ``Accept: application/json; version=1.0``
* Use a custom header ``X-Accept-Version: v1``

The ``FOSRestBundle`` allows you to use several of them at the same time or to choose one of them.

URI API versioning
------------------

If you want to version your api with the uri, you can simply use the symfony router:

.. code-block:: yaml

    # config/routes.yaml
    my_route:
        # ...
        path: /{version}/foo/route

Note: this will override the ``version`` attribute of the request if you use the ``FOSRestBundle`` versioning.

Configure ``FOSRestBundle`` to use the api versioning
-----------------------------------------------------

You should activate the versioning:

.. code-block:: yaml

    fos_rest:
        versioning: true

If you do not want to allow all the methods described above, you should choose which version resolver to enable:

.. code-block:: yaml

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

If you want to version by Accept header, you will need to do the following:

#. The format listener must be enabled

   See :doc:`Format Listener <format_listener>`

#. The client must pass the requested version in his header like this :

   .. code-block:: yaml

       Accept:application/json;version=1.0

#. You must configure the possible mime types for all supported versions:

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

   And then, import it from your Symfony config:

   .. code-block:: yaml

       imports:
           - { resource: fos_rest_mime_types.php }

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

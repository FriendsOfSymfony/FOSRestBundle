Full default annotations
========================

Param fetcher
-------------

QueryParam
~~~~~~~~~~

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\QueryParam;

    /**
     * @QueryParam(
     *   name="",
     *   key=null,
     *   requirements="",
     *   incompatibles={},
     *   default=null,
     *   description="",
     *   strict=false,
     *   map=false,
     *   nullable=false
     * )
     */

RequestParam
~~~~~~~~~~~~

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\RequestParam;

    /**
     * @RequestParam(
     *   name="",
     *   key=null,
     *   requirements="",
     *   default=null,
     *   description="",
     *   strict=true,
     *   map=false,
     *   nullable=false
     * )
     */

FileParam
~~~~~~~~~

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\FileParam;

    /**
     * @FileParam(
     *   name="",
     *   key=null,
     *   requirements={},
     *   default=null,
     *   description="",
     *   strict=true,
     *   nullable=false,
     *   image=false
     * )
     */

View
----

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\View;

    /**
     * @View(
     *  templateVar="",
     *  statusCode=null,
     *  serializerGroups={},
     *  populateDefaultVars=true,
     *  serializerEnableMaxDepthChecks=false
     * )
     */

Routing
-------

Route prefix
~~~~~~~~~~~~

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\Prefix;

    /**
     * @Prefix("")
     */

Route name prefix
~~~~~~~~~~~~~~~~~

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\NamePrefix;

    /**
     * @NamePrefix("")
     */

Route
~~~~~

RestBundle extends the `@Route Symfony annotation`_ from Symfony.

@Delete @Get @Head @Link @Patch @Post @Put @Unlink @Lock @Unlock @PropFind @PropPatch @Move @Mkcol @Copy are shortcuts to define
routes limited to a specific HTTP method. They have the same options as @Route.

.. _`@Route Symfony annotation`: https://symfony.com/doc/current/routing.html

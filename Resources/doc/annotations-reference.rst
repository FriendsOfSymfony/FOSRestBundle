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
     *   array=false,
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
     *   array=false,
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

@Delete @Get @Head @Link @Patch @Post @Put @Unlink @Lock @Unlock @PropFind @PropPatch @Move @Mkcol @Copy have the same options as @Route.

When using ``symfony/routing:>=2.4`` (or the full framework) you have access to
the expression language component and can add conditions to your routing
configuration with annotations (see `Routing Conditions`_).

Example syntax:

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\Route;

    /**
    * @Route("", condition="context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'")
    */

.. _`@Route Symfony annotation`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html
.. _`Routing Conditions`: http://symfony.com/doc/current/book/routing.html#book-routing-conditions

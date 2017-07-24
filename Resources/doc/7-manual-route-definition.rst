Manual definition of routes
===========================

If the automatic route generation does not fit your needs, you can manually
define a route using simple annotations. This is very helpful if you want to
have more than one url parameter without having a static word in between them.

For a full list of annotations check out ``FOS/RestBundle/Controller/Annotations``:

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\Delete;
    use FOS\RestBundle\Controller\Annotations\Head;
    use FOS\RestBundle\Controller\Annotations\Get;
    use FOS\RestBundle\Controller\Annotations\Patch;
    use FOS\RestBundle\Controller\Annotations\Options;
    use FOS\RestBundle\Controller\Annotations\Post;
    use FOS\RestBundle\Controller\Annotations\Put;

    /**
     * @Delete("/users/{id}")
     * @Head("/users/{id}")
     * @Get("/users/{id}")
     * @Patch("/users/{id}")
     * @Options("/users/{id}")
     * @Post("/users/{id}")
     * @Put("/users/{id}")
     */
    public function myAction()
    {
        // ...
    }

Method Name Prefix
------------------

By default, the routing name defined by the annotation is appended to the
generated routing name.

Example::

    use FOS\RestBundle\Controller\Annotations\Get

    /**
    * @Get("/users/foo", name="_foo")
    * @Get("/users/bar", name="_bar")
    */
    public function getUsersAction()
    {
        // ...
    }


Result:

===================  ======  ======  ====  ====================
Name                 Method  Scheme  Host  Path
===================  ======  ======  ====  ====================
get_users_foo        GET     ANY     ANY   /users/foo.{_format}
get_users_bar        GET     ANY     ANY   /users/bar.{_format}
===================  ======  ======  ====  ====================


You can add the ``method_prefix`` option to change this behavior.

Example::

    use FOS\RestBundle\Controller\Annotations\Get

    /**
    * @Get("/users/foo", name="get_foo", options={ "method_prefix" = false })
    * @Get("/users/bar", name="get_bar", options={ "method_prefix" = false })
    */
    public function getUsersAction()
    {
        // ...
    }


Result:

===================  ======  ======  ====  ====================
Name                 Method  Scheme  Host  Path
===================  ======  ======  ====  ====================
get_foo              GET      ANY    ANY   /users/foo.{_format}
get_bar              GET      ANY    ANY   /users/bar.{_format}
===================  ======  ======  ====  ====================

Or you can disable it globally by setting:

.. code-block:: yaml

    ...
    routing_loader:
        prefix_methods: false

Manual definition of routes
===========================

If the automatic route generation does not fit your needs, you can manually
define a route using simple annotations. This is very helpful if you want to
have more than one url parameter without having a static word in between them.

For a full list of annotations check out ``FOS/RestBundle/Controller/Annotations``:

.. code-block:: php

    // Delete Route Definition
    use FOS\RestBundle\Controller\Annotations\Delete;

    /**
     * DELETE Route annotation.
     * @Delete("/likes/{type}/{typeId}")
     */

    // Head Route Definition
    use FOS\RestBundle\Controller\Annotations\Head;

    /**
     * HEAD Route annotation.
     * @Head("/likes/{type}/{typeId}")
     */

    // Get Route Definition
    use FOS\RestBundle\Controller\Annotations\Get;

    /**
     * GET Route annotation.
     * @Get("/likes/{type}/{typeId}")
     */

    // Patch Route Definition
    use FOS\RestBundle\Controller\Annotations\Patch;

    /**
     * PATCH Route annotation.
     * @Patch("/likes/{type}/{typeId}")
     */
     
    // Options Route Definition
    use FOS\RestBundle\Controller\Annotations\Options;

    /**
     * OPTIONS Route annotation.
     * @Options("/likes/{type}/{typeId}")
     */
     

    // Post Route Definition
    use FOS\RestBundle\Controller\Annotations\Post;

    /**
     * POST Route annotation.
     * @Post("/likes/{type}/{typeId}")
     */

    // Put Route Definition
    use FOS\RestBundle\Controller\Annotations\Put;

    /**
     * PUT Route annotation.
     * @Put("/likes/{type}/{typeId}")
     */

Method Name Prefix
------------------

By default, the routing name defined by the annotation is appended to the
generated routing name.

Example:

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\Get

    /**
    * @Get("/users/foo", name="_foo")
    * @Get("/users/bar", name="_bar")
    */
    public function getUsers() { /** */ }


Result:

===================  ======  ======  ====  ====================
Name                 Method  Scheme  Host  Path
===================  ======  ======  ====  ====================
get_users_foo        GET     ANY     ANY   /users/foo.{_format}
get_users_bar        GET     ANY     ANY   /users/bar.{_format}
===================  ======  ======  ====  ====================


You can add the ``method_prefix`` option to change this behavior.

Example:

.. code-block:: php

    use FOS\RestBundle\Controller\Annotations\Get

    /**
    * @Get("/users/foo", name="get_foo", options={ "method_prefix" = false })
    * @Get("/users/bar", name="get_bar", options={ "method_prefix" = false })
    */
    public function getUsers() { /** */ }


Result:

===================  ======  ======  ====  ====================
Name                 Method  Scheme  Host  Path
===================  ======  ======  ====  ====================
get_foo              GET      ANY    ANY   /users/foo.{_format}
get_bar              GET      ANY    ANY   /users/bar.{_format}
===================  ======  ======  ====  ====================

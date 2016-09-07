Routing
=======

The RestBundle provides custom route loaders to help in defining REST friendly
routes as well as reducing the manual work of configuring routes and the given
requirements (like making sure that only GET may be used in certain routes
etc.).

You may specify a ``default_format`` that the routing loader will use for the
``_format`` parameter if none is specified.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        routing_loader:
            default_format: json

Many of the features explained below are used in the following example code:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/RestController.php

Single RESTful controller routes
--------------------------------

In this section we are looking at controllers for resources without sub-resources.
Handling of sub-resources requires some additional considerations which
are explained in the next section.

.. code-block:: yaml

    # app/config/routing.yml
    users:
        type:     rest
        host:     m.example.com
        resource: Acme\HelloBundle\Controller\UsersController

This will tell Symfony to automatically generate proper REST routes from your
``UsersController`` action names. Notice ``type: rest`` option. It's required so
that the RestBundle can find which routes are supported.

Define resource actions
-----------------------

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    class UsersController
    {
        public function copyUserAction($id) // RFC-2518
        {} // "copy_user"            [COPY] /users/{id}

        public function propfindUserPropsAction($id, $property) // RFC-2518
        {} // "propfind_user_props"  [PROPFIND] /users/{id}/props/{property}

        public function proppatchUserPropsAction($id, $property) // RFC-2518
        {} // "proppatch_user_props" [PROPPATCH] /users/{id}/props/{property}

        public function moveUserAction($id) // RFC-2518
        {} // "move_user"            [MOVE] /users/{id}

        public function mkcolUsersAction() // RFC-2518
        {} // "mkcol_users"          [MKCOL] /users

        public function optionsUsersAction()
        {} // "options_users"        [OPTIONS] /users

        public function getUsersAction()
        {} // "get_users"            [GET] /users

        public function newUsersAction()
        {} // "new_users"            [GET] /users/new

        public function postUsersAction()
        {} // "post_users"           [POST] /users

        public function patchUsersAction()
        {} // "patch_users"          [PATCH] /users

        public function getUserAction($slug)
        {} // "get_user"             [GET] /users/{slug}

        public function editUserAction($slug)
        {} // "edit_user"            [GET] /users/{slug}/edit

        public function putUserAction($slug)
        {} // "put_user"             [PUT] /users/{slug}

        public function patchUserAction($slug)
        {} // "patch_user"           [PATCH] /users/{slug}

        public function lockUserAction($slug)
        {} // "lock_user"            [LOCK] /users/{slug}

        public function unlockUserAction($slug)
        {} // "unlock_user"          [UNLOCK] /users/{slug}

        public function banUserAction($slug)
        {} // "ban_user"             [PATCH] /users/{slug}/ban

        public function removeUserAction($slug)
        {} // "remove_user"          [GET] /users/{slug}/remove

        public function deleteUserAction($slug)
        {} // "delete_user"          [DELETE] /users/{slug}

        public function getUserCommentsAction($slug)
        {} // "get_user_comments"    [GET] /users/{slug}/comments

        public function newUserCommentsAction($slug)
        {} // "new_user_comments"    [GET] /users/{slug}/comments/new

        public function postUserCommentsAction($slug)
        {} // "post_user_comments"   [POST] /users/{slug}/comments

        public function getUserCommentAction($slug, $id)
        {} // "get_user_comment"     [GET] /users/{slug}/comments/{id}

        public function editUserCommentAction($slug, $id)
        {} // "edit_user_comment"    [GET] /users/{slug}/comments/{id}/edit

        public function putUserCommentAction($slug, $id)
        {} // "put_user_comment"     [PUT] /users/{slug}/comments/{id}

        public function postUserCommentVoteAction($slug, $id)
        {} // "post_user_comment_vote" [POST] /users/{slug}/comments/{id}/votes

        public function removeUserCommentAction($slug, $id)
        {} // "remove_user_comment"  [GET] /users/{slug}/comments/{id}/remove

        public function deleteUserCommentAction($slug, $id)
        {} // "delete_user_comment"  [DELETE] /users/{slug}/comments/{id}

        public function linkUserFriendAction($slug, $id)
        {} // "link_user_friend"     [LINK] /users/{slug}/friends/{id}

        public function unlinkUserFriendAction($slug, $id)
        {} // "unlink_user_friend"     [UNLINK] /users/{slug}/friends/{id}
    }

That's all. All your resource (``UsersController``) actions will get mapped to
the proper routes as shown in the comments in the above example. Here are a few
things to note:

Implicit resource name definition
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

It's possible to omit the ``User`` part of the method names when the Controller
implements the ``ClassResourceInterface``. In this case FOSRestBundle can
determine the resource based on the Controller name. It's important to use
singular names in the Controller for this to work. By omitting the resource name
from the methods ``getUserAction`` and ``getUsersAction``, there would be an
overlap of method names. There is a special convention to call the methods
``getAction`` and ``cgetAction``, where the ``c`` stands for collection. So the
following would work as well:

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Routing\ClassResourceInterface;

    class UserController implements ClassResourceInterface
    {
        // ...

        public function cgetAction()
        {} // "get_users"     [GET] /users

        public function newAction()
        {} // "new_users"     [GET] /users/new

        public function getAction($slug)
        {} // "get_user"      [GET] /users/{slug}

        // ...
        public function getCommentsAction($slug)
        {} // "get_user_comments"    [GET] /users/{slug}/comments

        // ...
    }

It's also possible to override the resource name derived from the Controller
name via the ``@RouteResource`` annotation:


.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Controller\Annotations\RouteResource;

    /**
     * @RouteResource("User")
     */
    class FooController
    {
        // ...

        public function cgetAction()
        {} // "get_users"     [GET] /users

        public function newAction()
        {} // "new_users"     [GET] /users/new

        public function getAction($slug)
        {} // "get_user"      [GET] /users/{slug}

        // ...
        public function getCommentsAction($slug)
        {} // "get_user_comments"    [GET] /users/{slug}/comments

        // ...
    }

Finally, it's possible to have a singular resource name thanks to the ``@RouteResource`` annotation:


.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Controller\Annotations\RouteResource;

    /**
     * @RouteResource("User", pluralize=false)
     */
    class FooController
    {
        // ...

        public function cgetAction()
        {} // "cget_user"     [GET] /user

        public function newAction()
        {} // "new_user"     [GET] /user/new

        public function getAction($slug)
        {} // "get_user"      [GET] /user/{slug}

        // ...
        public function getCommentAction($slug)
        {} // "cget_user_comment"    [GET] /user/{slug}/comment

        // ...
    }

REST Actions
------------

There are 8 actions that have special meaning in regards to REST and have the
following behavior:

* **get** - this action accepts *GET* requests to the url ``/resources`` and returns
  all resources for this type. Shown as ``UsersController::getUsersAction()`` above.
  This action also accepts *GET* requests to the url ``/resources/{id}`` and
  returns a single resource for this type. Shown as ``UsersController::getUserAction()``
  above.
* **post** - this action accepts *POST* requests to the url ``/resources`` and
  creates a new resource of this type. Shown as ``UsersController::postUsersAction()``
  above.
* **put** - this action accepts *PUT* requests to the url ``/resources/{id}`` and
  updates a single resource for this type. Shown as ``UsersController::putUserAction()``
  above.
* **delete** - this action accepts *DELETE* requests to the url ``/resources/{id}``
  and deletes a single resource for this type. Shown as ``UsersController::deleteUserAction()``
  above.
* **patch** - this action accepts *PATCH* requests to the url ``/resources`` and
  is supposed to partially modify collection of resources (e.g. apply batch
  modifications to subset of resources). Shown as ``UsersController::patchUsersAction()``
  above. This action also accepts *PATCH* requests to the url ``/resources/{id}``
  and is supposed to partially modify the resource.
  Shown as ``UsersController::patchUserAction()`` above.
* **options** - this action accepts *OPTIONS* requests to the url ``/resources``
  and is supposed to return a list of REST resources that the user has access to.
  Shown as ``UsersController::optionsUsersAction()`` above.
* **link** - this action accepts *LINK* requests to the url ``/resources/{id}``
  and is supposed to return nothing but a status code indicating that the specified
  resources were linked. It is used to declare a resource as related to an other one.
  When calling a LINK url you must provide in your header at least one link header
  formatted as follow: ``<http://example.com/resources/{id}\>; rel="kind_of_relation"``
* **unlink** - this action accepts *UNLINK* requests to the url ``/resources/{id}``
  and is supposed to return nothing but a status code indicating that the specified
  resources were unlinked. It is used to declare that some resources are not
  related anymore. When calling a UNLINK url you must provide in your header at
  least one link header formatted as follow :
  ``<http://example.com/resources/{id}\>; rel="kind_of_relation"``

Important note about **link** and **unlink**: The implementation of the request
listener extracting the resources as entities is not provided by this bundle. A
good implementation can be found here: `REST APIs with Symfony2: The Right Way`_
It also contains some examples on how to use it. **link** and **unlink** were
obsoleted by RFC 2616, RFC 5988 aims to define it in a more clear way. Using
these methods is not risky, but remains unclear (cf. issues 323 and 325).

Conventional Actions
--------------------

HATEOAS, or Hypermedia as the Engine of Application State, is an aspect of REST
which allows clients to interact with the REST service with hypertext - most
commonly through an HTML page. There are 3 Conventional Action routings that are
supported by this bundle:

* **new** - A hypermedia representation that acts as the engine to *POST*.
  Typically this is a form that allows the client to *POST* a new resource.
  Shown as ``UsersController::newUsersAction()`` above.
* **edit** - A hypermedia representation that acts as the engine to *PUT*.
  Typically this is a form that allows the client to *PUT*, or update, an
  existing resource. Shown as ``UsersController::editUserAction()`` above.
* **remove** - A hypermedia representation that acts as the engine to *DELETE*.
  Typically this is a form that allows the client to *DELETE* an existing resource.
  Commonly a confirmation form. Shown as ``UsersController::removeUserAction()``
  above.

Custom PATCH Actions
--------------------

All actions that do not match the ones listed in the sections above will
register as a *PATCH* action. In the controller shown above, these actions are
``UsersController::lockUserAction()``, ``UsersController::banUserAction()`` and
``UsersController::voteUserCommentAction()``. You could just as easily create a
method called ``UsersController::promoteUserAction()`` which would take a
*PATCH* request to the url ``/users/{slug}/promote``. This allows for easy
updating of aspects of a resource, without having to deal with the resource as a
whole at the standard *PATCH* or *PUT* endpoint.

Sub-Resource Actions
--------------------

Of course it's possible and common to have sub or child resources. They are
easily defined within the same controller by following the naming convention
``ResourceController::actionResourceSubResource()`` - as seen in the example
above with ``UsersController::getUserCommentsAction()``. This is a good strategy
to follow when the child resource needs the parent resource's ID in order to
look up itself.

Optional {_format} in route
---------------------------

By default, routes are generated with ``{_format}`` string. If you want to get clean
urls (``/orders`` instead ``/orders.{_format}``) then all you have to do is add
some configuration:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        routing_loader:
            include_format:       false

The ``{_format}`` route requirement is automatically positioned using the available
listeners. So by default, the  requirement will be ``{json|xml|html}``. If you want
to limit or add a custom format, you can do so by overriding it with the
``@Route`` annotation (or another one extending it, like ``@Get``, ``@Post``, ...):

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Controller\Annotations\Route;

        // ...

        /**
         * @Route(requirements={"_format"="json|xml"})
         */
        public function getAction($slug)
        {}

        // ...
    }

Changing pluralization in generated routes
------------------------------------------

If you want to change pluralization in generated routes, you can do this by
replacing ``fos_rest.inflector.doctrine`` service with your own implementation.
Create a new class that implements ``FOS\RestBundle\Inflector\InflectorInterface``.

The example below will remove pluralization by implementing the interface and
returning the ``$word`` instead of executing method ``Inflector::pluralize($word);``
Example class implementing ``InflectorInterface``:

.. code-block:: php

    <?php

    namespace Acme\HelloBundle\Util\Inflector;

    use FOS\RestBundle\Inflector\InflectorInterface;

    /**
     * Inflector class
     *
     */
    class NoopInflector implements InflectorInterface
    {
        public function pluralize($word)
        {
            // Don't pluralize
            return $word;
        }
    }

Define your service in ``config.yml``:

.. code-block:: yaml

    services:
        acme.hellobundle.util.inflector:
          class: Acme\HelloBundle\Util\Inflector\NoopInflector

Tell ``fos_rest`` to use your own service as inflector, also in ``config.yml``:

.. code-block:: yaml

    fos_rest:
        service:
            inflector: acme.hellobundle.util.inflector

That was it!

.. _`REST APIs with Symfony2: The Right Way`: http://williamdurand.fr/2012/08/02/rest-apis-with-symfony2-the-right-way/

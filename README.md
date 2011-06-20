RestBundle
==========

This bundle provides various tools to rapidly develop RESTful API's & applications with Symfony2.

Its currently under development so key pieces that are planned are still missing.

For now the Bundle provides a view layer to enable output format agnostic Controllers,
which includes the ability to handle redirects differently based on a service container
aware Serializer service that can lazy load encoders and normalizers.

Furthermore a custom route loader can be used to when following a method
naming convention to automatically provide routes for multiple actions by simply
configuring the name of a controller.

It also has support for RESTful decoding of HTTP request body and Accept headers
as well as a custom Exception controller that assists in using appropriate HTTP
status codes.

Eventually the bundle will also provide normalizers for form and validator instances as
well as provide a solution to generation end user documentation describing the REST API.

Installation
============

    1. Add this bundle to your project as a Git submodule:

        $ git submodule add git://github.com/FriendsOfSymfony/RestBundle.git vendor/bundles/FOS/RestBundle

    2. Add the FOS namespace to your autoloader:

        // app/autoload.php
        $loader->registerNamespaces(array(
            'FOS' => __DIR__.'/../vendor/bundles',
            // your other namespaces
        ));

    3. Add this bundle to your application's kernel:

        // application/ApplicationKernel.php
        public function registerBundles()
        {
          return array(
              // ...
              new FOS\RestBundle\FOSRestBundle(),
              // ...
          );
        }

Examples
========

The LiipHelloBundle provides several examples for the RestBundle:
https://github.com/liip/HelloBundle

There is also a fork of the Symfony2 Standard Edition that is configured to show the LiipHelloBundle examples:
https://github.com/lsmith77/symfony-standard/tree/techtalk

Configuration
=============

Basic configuration
-------------------

The RestBundle allows adapting several classes it uses. Alternatively entire
services may be adapted. In the following examples the default Json encoder class
is modified and a custom serializer service is configured:

    # app/config.yml
    fos_rest:
        classes:
            json: MyProject\MyBundle\Serializer\Encoder\JsonEncoder
        services:
            serializer: my.serializer

Note the service for the RSS encoder needs to be defined in a custom bundle:

    <service id="my.encoder.rss" class="MyProject\MyBundle\Serializer\Encoder\RSSEncoder" />

View support
------------

Registering a custom encoder requires modifying your configuration options.
Following is an example adding support for a custom RSS encoder while removing
support for xml.

When using View::setResourceRoute() the default behavior of forcing
a redirect to the route for html is disabled.

The default JSON encoder class is modified and a custom serializer service
is configured.

The a default normalizer is registered with the ``fos_rest.get_set_method_normalizer`.

Also a default key for any form instances inside view parameters is set to ``form``.

Finally the HTTP response status code for failed validation is set to ``400``:

    # app/config.yml
    fos_rest:
        formats:
            rss: my.encoder.rss
            xml: false
        force_redirects:
            html: false
        normalizers:
            - "fos_rest.get_set_method_normalizer"
        default_form_key: form
        failed_validation: HTTP_BAD_REQUEST

Listener support
----------------

To enable the Request body decoding and Request format listener simply adapt your configuration as follows:

    # app/config.yml
    fos_rest:
        format_listener: true
        body_listener: true

In the behavior of the format listener can be configured in a more granular fashion.
Below you can see the defaults in case ``format_listener`` is set to true as above:

    # app/config.yml
    fos_rest:
        format_listener:
            default_priorities:
                - html
                - "*/*"
            fallback_format: html

You may also specify a ``default_format`` that the routing loader will use for 
the ``_format`` parameter if none is specified.

    # app/config.yml
    fos_rest:
        routing_loader:
            default_format: json

Note that setting ``default_priorities`` to a non empty array enables Accept header negotiations.
Also note in case for example more complex Accept header negotiations are required, the user should
either set a custom ``ControllerListener`` class or register their own "onCoreController" event.

    # app/config.yml
    fos_rest:
        classes:
            format_listener: MyProject\MyBundle\Controller\ControllerListener

Note see the section about the view support in regards to how to register/deregister
encoders for specific formats as the request body decoding uses encoders for decoding.

SensioFrameworkExtraBundle support
----------------------------------

This requires adding the SensioFrameworkExtraBundle to you vendors:

    $ git submodule add git://github.com/sensio/SensioFrameworkExtraBundle.git vendor/bundles/Sensio/Bundle/FrameworkExtraBundle

Make sure to disable view annotations in the SensioFrameworkExtraBundle config,
enable or disable any of the other features depending on your needs:

    # app/config.yml
    sensio_framework_extra:
        view:    { annotations: false }
        router:  { annotations: true }

Finally enable the SensioFrameworkExtraBundle listener in the RestBundle:

    # app/config.yml
    fos_rest:
        frameworkextra_bundle: true

JMSSerializerBundle support
---------------------------

Note: Temporarily please use this fork https://github.com/lsmith77/SerializerBundle/tree/use_core

This requires adding the JMSSerializerBundle to you vendors:

    $ git submodule add git://github.com/schmittjoh/SerializerBundle.git vendor/bundles/JMS/SerializerBundle

Finally enable the JMSSerializerBundle support in the RestBundle:

    # app/config.yml
    fos_rest:
        serializer_bundle: true

When using JMSSerializerBundle the ``normalizers`` config option is ignored as in this case
annotations should be used to register specific normalizers for a given class.

ExceptionController support
---------------------------

The RestBundle view layer aware ExceptionController is enabled as follows:

    # app/config.yml
    framework:
        exception_controller: "FOS\RestBundle\Controller\ExceptionController::showAction"

To map Exception classes to HTTP response status codes an ``exception_map`` may be configured,
where the keys match a fully qualified class name and the values are either an integer HTTP response
status code or a string matching a class constant of the ``FOS\RestBundle\Response\Codes`` class:

    # app/config.yml
    fos_rest:
        exception:
            codes:
                "Symfony\Component\Routing\Matcher\Exception\NotFoundException": 404
                "Doctrine\ORM\OptimisticLockException": HTTP_CONFLICT
            messages:
                "Acme\HelloBundle\Exception\MyExceptionWithASafeMessage": true

Routing
=======

The RestBundle provides custom route loaders to help in defining REST friendly routes.

Single RESTful controller routes
--------------------------------

    # app/config/routing.yml
    users:
      type:     rest
      resource: Acme\HelloBundle\Controller\UsersController

This will tell Symfony2 to automatically generate proper REST routes from your `UsersController` action names.
Notice `type: rest` option. It's required so that the RestBundle can find which routes are supported.

## Define resource actions

    class UsersController extends Controller
    {
        public function getUsersAction()
        {} // `get_users`    [GET] /users

        public function newUsersAction()
        {} // `new_users`    [GET] /users/new

        public function postUsersAction()
        {} // `post_users`   [POST] /users

        public function patchUsersAction()
        {} // `patch_users`   [PATCH] /users

        public function getUserAction($slug)
        {} // `get_user`     [GET] /users/{slug}

        public function editUserAction($slug)
        {} // `edit_user`    [GET] /users/{slug}/edit

        public function putUserAction($slug)
        {} // `put_user`     [PUT] /users/{slug}

        public function patchUserAction($slug)
        {} // `patch_user`   [PATCH] /users/{slug}

        public function lockUserAction($slug)
        {} // `lock_user`    [PUT] /users/{slug}/lock

        public function banUserAction($slug, $id)
        {} // `ban_user`     [PUT] /users/{slug}/ban

        public function removeUserAction($slug)
        {} // `remove_user`  [GET] /users/{slug}/remove

        public function deleteUserAction($slug)
        {} // `delete_user`  [DELETE] /users/{slug}

        public function getUserCommentsAction($slug)
        {} // `get_user_comments`    [GET] /users/{slug}/comments

        public function newUserCommentsAction($slug)
        {} // `new_user_comments`    [GET] /users/{slug}/comments/new

        public function postUserCommentsAction($slug)
        {} // `post_user_comments`   [POST] /users/{slug}/comments

        public function getUserCommentAction($slug, $id)
        {} // `get_user_comment`     [GET] /users/{slug}/comments/{id}

        public function editUserCommentAction($slug, $id)
        {} // `edit_user_comment`    [GET] /users/{slug}/comments/{id}/edit

        public function putUserCommentAction($slug, $id)
        {} // `put_user_comment`     [PUT] /users/{slug}/comments/{id}

        public function voteUserCommentAction($slug, $id)
        {} // `vote_user_comment`    [PUT] /users/{slug}/comments/{id}/vote

        public function removeUserCommentAction($slug, $id)
        {} // `remove_user_comment`  [GET] /users/{slug}/comments/{id}/remove

        public function deleteUserCommentAction($slug, $id)
        {} // `delete_user_comment`  [DELETE] /users/{slug}/comments/{id}
    }


That's all. All your resource (`UsersController`) actions will get mapped to the proper routes
as shown in the comments in the above example. Here are a few things to note:

### REST Actions

There are 5 actions that have special meaning in regards to REST and have the following behavior:

* **get** - this action accepts *GET* requests to the url */resources* and returns all resources for this type. Shown as
`UsersController::getUsersAction()` above. This action also accepts *GET* requests to the url */resources/{id}* and
returns a single resource for this type. Shown as `UsersController::getUserAction()` above.
* **post** - this action accepts *POST* requests to the url */resources* and creates a new resource of this type. Shown
as `UsersController::postUsersAction()` above.
* **put** - this action accepts *PUT* requests to the url */resources/{id}* and updates a single resource for this type.
Shown as `UsersController::putUserAction()` above.
* **delete** - this action accepts *DELETE* requests to the url */resources/{id}* and deltes a single resource for this
type. Shown as `UsersController::deleteUserAction()` above.
* **patch** - this action accepts *PATCH* requests to the url */resources* and is supposed to partially modify collection
of resources (e.g. apply batch modifications to subset of resources). Shown as `UsersController::patchUsersAction()` above.
This action also accepts *PATCH* requests to the url */resources/{id}* and is supposed to partially modify the resource. 
Shown as `UsersController::patchUserAction()` above.

### HATEOAS Actions

HATEOAS, or Hypermedia as the Engine of Application State, is an aspect of REST which allows clients to interact with the
REST service through hypertext - most commonly through an HTML page. There are 3 HATEOAS actions routings that are
supported by this bundle:

* **new** - A hypermedia representation that acts as the engine to *POST*. Typically this is a form that allows the client
to *POST* a new resource. Shown as `UsersController::newUsersAction()` above.
* **edit** - A hypermedia representation that acts as the engine to *PUT*. Typically this is a form that allows the client
to *PUT*, or update, an existing resource. Shown as `UsersController::editUserAction()` above.
* **remove** - A hypermedia representation that acts as the engine to *DELETE*. Typically this is a form that allows the
client to *DELETE* an existing resource. Commonly a confirmation form. Shown as `UsersController::deleteUserAction()` above.

### Custom PUT Actions

All actions that do not match the ones listed in the sections above will register as a *PUT* action. In the controller
shown above, these actions are `UsersController::lockUserAction()` and `UsersController::banUserAction()`. You could
just as easily create a method called `UsersController::promoteUserAction()` which would take a *PUT* request to the url
*/users/{slug}/promote*. This allows for easy updating of aspects of a resource, without having to deal with the
resource as a whole at the standard *PUT* endpoint.

### Sub-Resource Actions

Of course it's possible and common to have sub or child resources. They are easily defined within the same controller by
following the naming convention `ResourceController::actionResourceSubResource()` - as seen in the example above with
`UsersController::getUserCommentsAction()`. This is a good strategy to follow when the child resource needs the parent
resource's ID in order to look up itself. 

Relational RESTful controllers routes
-------------------------------------

Sometimes it's better to place subresource actions in their own controller, especially when
you have more than 2 subresource actions.

## Resource collection

In this case, you must first specify resource relations in special rest YML or XML collection:

    # src/Acme/HelloBundle/Resources/config/users_routes.yml
    users:
      type:     rest
      resource: "@AcmeHello\Controller\UsersController"
    
    comments:
      type:     rest
      parent:   users
      resource: "@AcmeHello\Controller\CommentsController"

Notice `parent: users` option in the second case. This option specifies that the comments resource
is child of the users resource. In this case, your `UsersController` MUST always have a single
resource `get...` action:

    class UsersController extends Controller
    {
        public function getUserAction($slug)
        {} // `get_user`   [GET] /users/{slug}
    
        ...
    }

It's used to determine the parent collection name. Controller name itself not used in routes
auto-generation process and can be any name you like.

## Define child resource controller

`CommentsController` actions now will looks like:

    class CommentsController extends Controller
    {
        public function voteCommentAction($slug, $id)
        {} // `vote_user_comment`   [PUT] /users/{slug}/comments/{id}/vote
        
        public function getCommentsAction($slug)
        {} // `get_user_comments`   [GET] /users/{slug}/comments
        
        public function getCommentAction($slug, $id)
        {} // `get_user_comment`    [GET] /users/{slug}/comments/{id}
        
        public function deleteCommentAction($slug, $id)
        {} // `delete_user_comment` [DELETE] /users/{slug}/comments/{id}
        
        public function newCommentsAction($slug)
        {} // `new_user_comments`   [GET] /users/{slug}/comments/new

        public function editCommentAction($slug, $id)
        {} // `edit_user_comment`   [GET] /users/{slug}/comments/{id}/edit

        public function removeCommentAction($slug, $id)
        {} // `remove_user_comment` [GET] /users/{slug}/comments/{id}/remove
    }

Notice, we got rid of the `User` part in action names. That is because the RestBundle routing
already knows, that `CommentsController::...` is child resources of `UsersController::getUser()`
resource.

## Include resource collections in application routing

Last step is mapping of your collection routes into the application `routing.yml`:

    # app/config/routing.yml
    users:
      type:     rest
      resource: "@AcmeHello/Resources/config/users_routes.yml"

That's all. Note that it's important to use the `type: rest` param when including your application's
routing file. Without it, rest routes will still work but resource collections will fail. If you get an
exception that contains `...routing loader does not support given key: "parent"...` then you are most likely missing
the `type: rest` param in your application level routes include.

## Routes naming

RestBundle uses REST paths to generate route name. This means, that URL:

    [PUT] /users/{slug}/comments/{id}/vote

will become the route with the name:

    vote_user_comment

For further examples, see comments of controllers in the code above.

### Naming collisions

Sometimes, routes auto-naming will lead to route names collisions, so RestBundle route
collections provides a `name_prefix` (`name-prefix` for xml and @NamePrefix for
annotations) parameter:

    # src/Acme/HelloBundle/Resources/config/users_routes.yml
    comments:
      type:         rest
      resource:     "@AcmeHello\Controller\CommentsController"
      name_prefix:  api_

With this configuration, route name would become:

    api_vote_user_comment

Say NO to name collisions!

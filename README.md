RestBundle
==========

This bundle provides various tools to rapidly develop RESTful API's with Symfony2.

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

Configuration
=============

Basic configuration
-------------------

The RestBundle allows adapting several classes it uses. Alternatively entire
services may be adapted. In the following examples the default Json encoder class
is modified and a custom serializer service is configured:

    # app/config.yml
    fos_rest:
        class:
            json: MyProject\MyBundle\Serializer\Encoder\JsonEncoder
        service:
            serializer: my.serializer

Note the service for the RSS encoder needs to be defined in a custom bundle:

    <service id="my.encoder.rss" class="MyProject\MyBundle\Serializer\Encoder\RSSEncoder" />

View support
------------

Registering a custom encoder requires modifying your configuration options.
Following is an example adding support for a custom RSS encoder while removing
support for xml. Also the default Json encoder class is modified and a custom
serializer service is configured and the a normalizer is registered
for the class ``Acme\HelloBundle\Document\Article``. Finally the HTTP response
status code for failed validation is set to ``400``:

    # app/config.yml
    fos_rest:
        formats:
            rss: my.encoder.rss
            xml: false
        normalizers:
            'Acme\HelloBundle\Document\Article': 'my.get_set_method_normalizer'
        failed_validation: HTTP_BAD_REQUEST

Request listener support
------------------------

To enable the request listener simply adapt your configuration as follows:

    # app/config.yml
    fos_rest:
        format_listener: true

In the behavior of the request listener can be configured in a more granular fashion:

    # app/config.yml
    fos_rest:
        format_listener:
            detect_format: true
            decode_body: true
            default_format: json

Note in case for example more complex Accept header negotiations are required, the user
should either set a custom RequestListener class or register their own "onCoreRequest" event.

    # app/config.yml
    fos_rest:
        class:
            request_format_listener: MyProject\MyBundle\View\RequestListener

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
        frameworkextra: true

ExceptionController support
---------------------------

The RestBundle view layer aware ExceptionController is enabled as follows:

    # app/config.yml
    framework:
        exception_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'

To map Exception classes to HTTP response status codes an ``exception_map`` may be configured,
where the keys match a fully qualified class name and the values are either an integer HTTP response
status code or a string matching a class constant of the ``FOS\RestBundle\Response\Codes`` class:

    # app/config.yml
    fos_rest:
        exception:
            codes:
                'Symfony\Component\Routing\Matcher\Exception\NotFoundException': 404
                'Doctrine\ORM\OptimisticLockException': HTTP_CONFLICT
            messages:
                'Acme\HelloBundle\Exception\MyExceptionWithASafeMessage': true

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
        {} // `get_users`   [GET] /users
    
        public function getUserAction($slug)
        {} // `get_user`    [GET] /users/{slug}
    
        public function postUsersAction()
        {} // `post_users`  [POST] /users
    
        public function putUserAction($slug)
        {} // `put_user`    [PUT] /users/{slug}
    
        public function lockUserAction($slug)
        {} // `lock_user`   [PUT] /users/{slug}/lock
    
        public function newUsersAction()
        {} // `new_users`   [GET] /users/new
    
        public function banUserAction($slug, $id)
        {} // `ban_user`    [PUT] /users/{slug}/ban
    
        public function voteUserCommentAction($slug, $id)
        {} // `vote_user_comment`   [PUT] /users/{slug}/comments/{id}/vote
    
        public function getUserCommentsAction($slug)
        {} // `get_user_comments`   [GET] /users/{slug}/comments
    
        public function getUserCommentAction($slug, $id)
        {} // `get_user_comment`    [GET] /users/{slug}/comments/{id}
    
        public function deleteUserCommentAction($slug, $id)
        {} // `delete_user_comment` [DELETE] /users/{slug}/comments/{id}
    
        public function newUserCommentsAction($slug)
        {} // `new_user_comments`   [GET] /users/{slug}/comments/new
    }

That's all. All your resource (`UsersController`) actions will get mapped to the proper routes
as shown in the comments in the above example.

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

That's all.

## Routes naming

RestBundle uses REST paths to generate route name. This means, that URL:

    [PUT] /users/{slug}/comments/{id}/vote

will become the route with the name:

    vote_user_comment

For further examples, see comments of controllers in the code above.

### Naming collisions

Sometimes, routes auto-naming will lead to route names collisions, so RestBundle route
collections provides a `name_prefix` (`name-prefix` for xml and @rest:NamePrefix for
annotations) parameter:

    # src/Acme/HelloBundle/Resources/config/users_routes.yml
    comments:
      type:         rest
      resource:     "@AcmeHello\Controller\CommentsController"
      name_prefix:  api_

With this configuration, route name would become:

    api_vote_user_comment

Say NO to name collisions!

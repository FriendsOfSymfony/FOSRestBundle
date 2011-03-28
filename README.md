RestBundle
==========

This Bundle provides various tools to rapidly develop RESTful API's with Symfony2.

Its currently under development so key pieces that are planned are still missing.

For now the Bundle provides a view layer to enable output format agnostic Controllers,
which includes the ability to handle redirects differently.

Furthermore a custom route loader can be used to when following a method
naming convention to automatically provide routes for multiple actions by simply
configuring the name of a controller.

Eventually the goal is to also support RESTful decoding of request headers and body,
serializing of form's into different formats and assisting in returning correct
HTTP status codes. Generation of REST API end user documentation is also a goal.

Installation
============

    1. Add this bundle to your project as a Git submodule:

        $ git submodule add git://github.com/fos/RestBundle.git vendor/bundles/FOS/RestBundle

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
-------------

Registering a custom encoder requires modifying your configuration options.
Following is an example adding support for a custom RSS encoder while removing
support for xml. Also the default Json encoder class is modified:

    # app/config.yml
    fos_rest:
        fos_rest.formats:
            rss: my.encoder.rss
            xml: false
        class:
            json: MyProject\MyBundle\Serializer\Encoder\JsonEncoder

Note the service for the RSS encoder needs to be defined in a custom bundle:

    <service id="my.encoder.rss" class="MyProject\MyBundle\Serializer\Encoder\RSSEncoder" />

FrameworkBundle support
-----------------------

Make sure to disable view annotations in the FrameworkBundle config, enable
or disable any of the other features depending on your needs:

    sensio_framework_extra:
        view:    { annotations: false }
        router:  { annotations: true }

Finally enable the FrameworkBundle listener in the RestBundle:

    fos_rest:
        frameworkextra: true

Routing
=======

## Single RESTful controller routes

    # app/confg/routing.yml
    users:
      type:     rest
      resource: Application\HelloBundle\Controller\UsersController

This will tell Symfony2 to automatically generate proper REST routes from your `UsersController` action names.
Notice `type:     rest` option. It's required so that the RestBundle can find which routes are supported.

### Define resource actions

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

That's all. All your resource (`UsersController`) actions will get mapped to proper routes (commented examples).

## Relational RESTful controllers routes

Sometimes it's better to place subresource actions in it's own controller. Especially when
you have more than 2 subresource actions.

### Resource collection

In this case, you must first specify resource relations in special rest YML or XML collection:

    # src/Acme/HelloBundle/Resources/config/users_routes.yml
    users:
      type:     rest
      resource: "@AcmeHello\Controller\UsersController"
    
    comments:
      type:     rest
      parent:   users
      resource: "@AcmeHello\Controller\CommentsController"

Notice `parent:   users` option in second case. This option specifies that comments resource is
child of users resource. In this case, your `UsersController` MUST always have single resource
`get...` action:

    class UsersController extends Controller
    {
        public function getUserAction($slug)
        {} // `get_user`   [GET] /users/{slug}
    
        ...
    }

It's used to determine parent collection name. Controller name itself not used in routes
auto-generation process & can be any name you like.

### Define child resource controller

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

Notice, that we get rid of `User` part in action names. It's because RestBundle routing
already knows, that `CommentsController::...` is child resources of `UsersController::getUser()`
resource.

### Include resource collections in application routing

Last step is mapping of your collection routes into application `routing.yml`:

    # app/confg/routing.yml
    users:
      type:     rest
      resource: "@AcmeHello/Resources/config/users_routes.yml"

That's all.

### Routes naming

RestBundle uses REST path to generate route name. It means, that URL:

    [PUT] /users/{slug}/comments/{id}/vote

will become route with name:

    vote_user_comment

For further examples, see comments of controllers code above.

#### Naming collisions

Sometimes, routes auto-naming will lead to route names collisions, so RestBundle route
collections provides a `name_prefix` (`name-prefix` for xml) parameter:

    # src/Acme/HelloBundle/Resources/config/users_routes.yml
    comments:
      type:         rest
      resource:     "@AcmeHello\Controller\CommentsController"
      name_prefix:  api_

With this configuration, route name would become:

    api_vote_user_comment

Say NO to name collisions!

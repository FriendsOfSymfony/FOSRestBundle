Routing
=======

The RestBundle provides custom route loaders to help in defining REST friendly
routes as well as reducing the manual work of configuring routes and the given
requirements (like making sure that only GET may be used in certain routes
etc.).

You may specify a ``default_format`` that the routing loader will use for the
``_format`` parameter if none is specified.

```yaml
# app/config/config.yml
fos_rest:
    routing_loader:
        default_format: json
```

Many of the features explained below are used in the following example code:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/RestController.php

Single RESTful controller routes
================================

```yaml
# app/config/routing.yml
users:
    type:     rest
    resource: Acme\HelloBundle\Controller\UsersController
```

This will tell Symfony2 to automatically generate proper REST routes from your ``UsersController`` action names.
Notice ``type: rest`` option. It's required so that the RestBundle can find which routes are supported.
Notice ``name_prefix: my_bundle_`` option. It's useful to prefix the generated controller routes. Take care that
you can use ``name_prefix`` on an import only when the file is imported itself with the type ``rest``.

## Define resource actions

```php
<?php
class UsersController extends Controller
{
    public function getUsersAction()
    {} // "get_users"    [GET] /users

    public function newUsersAction()
    {} // "new_users"    [GET] /users/new

    public function postUsersAction()
    {} // "post_users"   [POST] /users

    public function patchUsersAction()
    {} // "patch_users"   [PATCH] /users

    public function getUserAction($slug)
    {} // "get_user"     [GET] /users/{slug}

    public function editUserAction($slug)
    {} // "edit_user"    [GET] /users/{slug}/edit

    public function putUserAction($slug)
    {} // "put_user"     [PUT] /users/{slug}

    public function patchUserAction($slug)
    {} // "patch_user"   [PATCH] /users/{slug}

    public function lockUserAction($slug)
    {} // "lock_user"    [PATCH] /users/{slug}/lock

    public function banUserAction($slug, $id)
    {} // "ban_user"     [PATCH] /users/{slug}/ban

    public function removeUserAction($slug)
    {} // "remove_user"  [GET] /users/{slug}/remove

    public function deleteUserAction($slug)
    {} // "delete_user"  [DELETE] /users/{slug}

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
    {} // "post_user_comment_vote" [POST] /users/{slug}/comments/{id}/vote

    public function removeUserCommentAction($slug, $id)
    {} // "remove_user_comment"  [GET] /users/{slug}/comments/{id}/remove

    public function deleteUserCommentAction($slug, $id)
    {} // "delete_user_comment"  [DELETE] /users/{slug}/comments/{id}
}
```

That's all. All your resource (``UsersController``) actions will get mapped to the proper routes
as shown in the comments in the above example. Here are a few things to note:

### REST Actions

There are 5 actions that have special meaning in regards to REST and have the following behavior:

* **get** - this action accepts *GET* requests to the url */resources* and returns all resources for this type. Shown as
``UsersController::getUsersAction()`` above. This action also accepts *GET* requests to the url */resources/{id}* and
returns a single resource for this type. Shown as ``UsersController::getUserAction()`` above.
* **post** - this action accepts *POST* requests to the url */resources* and creates a new resource of this type. Shown
as ``UsersController::postUsersAction()`` above.
* **put** - this action accepts *PUT* requests to the url */resources/{id}* and updates a single resource for this type.
Shown as ``UsersController::putUserAction()`` above.
* **delete** - this action accepts *DELETE* requests to the url */resources/{id}* and deltes a single resource for this
type. Shown as ``UsersController::deleteUserAction()`` above.
* **patch** - this action accepts *PATCH* requests to the url */resources* and is supposed to partially modify collection
of resources (e.g. apply batch modifications to subset of resources). Shown as ``UsersController::patchUsersAction()`` above.
This action also accepts *PATCH* requests to the url */resources/{id}* and is supposed to partially modify the resource. 
Shown as ``UsersController::patchUserAction()`` above.

### Conventional Actions

HATEOAS, or Hypermedia as the Engine of Application State, is an aspect of REST which allows clients to interact with the
REST service with hypertext - most commonly through an HTML page. There are 3 Conventional Action routings that are
supported by this bundle:

* **new** - A hypermedia representation that acts as the engine to *POST*. Typically this is a form that allows the client
to *POST* a new resource. Shown as ``UsersController::newUsersAction()`` above.
* **edit** - A hypermedia representation that acts as the engine to *PUT*. Typically this is a form that allows the client
to *PUT*, or update, an existing resource. Shown as ``UsersController::editUserAction()`` above.
* **remove** - A hypermedia representation that acts as the engine to *DELETE*. Typically this is a form that allows the
client to *DELETE* an existing resource. Commonly a confirmation form. Shown as ``UsersController::removeUserAction()`` above.

### Custom PATCH Actions

All actions that do not match the ones listed in the sections above will register as a *PATCH* action. In the controller
shown above, these actions are ``UsersController::lockUserAction()``, ``UsersController::banUserAction()`` and 
``UsersController::voteUserCommentAction()``. You could just as easily create a method called
``UsersController::promoteUserAction()`` which would take a *PATCH* request to the url */users/{slug}/promote*.
This allows for easy updating of aspects of a resource, without having to deal with the resource as a whole at
the standard *PATCH* or *PUT* endpoint.

### Sub-Resource Actions

Of course it's possible and common to have sub or child resources. They are easily defined within the same controller by
following the naming convention ``ResourceController::actionResourceSubResource()`` - as seen in the example above with
``UsersController::getUserCommentsAction()``. This is a good strategy to follow when the child resource needs the parent
resource's ID in order to look up itself. 

## That was it!
[Return to the index](index.md) or continue reading about [Automatic route generation: multiple RESTful controllers](6-automatic-route-generation_multiple-restful-controllers.md).

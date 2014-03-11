Relational RESTful controllers routes
=====================================

Sometimes it's better to place subresource actions in their own controller, especially when
you have more than 2 subresource actions.

## Resource collection

In this case, you must first specify resource relations in special rest YML or XML collection:

```yaml
# src/Acme/HelloBundle/Resources/config/users_routes.yml
users:
    type:     rest
    resource: Acme\HelloBundle\Controller\UsersController

comments:
    type:     rest
    parent:   users
    resource: Acme\HelloBundle\Controller\CommentsController
```

```xml
<!-- src/Acme/HelloBundle/Resources/config/users_routes.xml -->
<?xml version="1.0" encoding="UTF-8" ?>

<routes xmlns="http://friendsofsymfony.github.com/schema/rest"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://friendsofsymfony.github.com/schema/rest https://raw.github.com/FriendsOfSymfony/FOSRestBundle/master/Resources/config/schema/routing/rest_routing-1.0.xsd">

    <import id="users" type="rest" resource="Acme\HelloBundle\Controller\UsersController" />
    <import type="rest" parent="users" resource="Acme\HelloBundle\Controller\CommentsController" />
</routes>
```

Notice ``parent: users`` option in the second case. This option specifies that the comments resource
is child of the users resource.

It is also necessary to add ``type: rest`` to the ``routing.yml`` file:

```yaml
# app/config/routing.yml
acme_hello:
    type: rest
    resource: "@AcmeHelloBundle/Resources/config/users_routes.yml"
```

In this case, your ``UsersController`` MUST always have a single resource ``get...`` action:

```php
<?php
class UsersController extends Controller
{
    public function getUserAction($slug)
    {} // "get_user"   [GET] /users/{slug}

    ...
}
```

It's used to determine the parent collection name. Controller name itself not used in routes
auto-generation process and can be any name you like.

## Define child resource controller

``CommentsController`` actions now will looks like:

```php
<?php
class CommentsController extends Controller
{
    public function postCommentVoteAction($slug, $id)
    {} // "post_user_comment_vote" [POST] /users/{slug}/comments/{id}/vote

    public function getCommentsAction($slug)
    {} // "get_user_comments"   [GET] /users/{slug}/comments

    public function getCommentAction($slug, $id)
    {} // "get_user_comment"    [GET] /users/{slug}/comments/{id}

    public function deleteCommentAction($slug, $id)
    {} // "delete_user_comment" [DELETE] /users/{slug}/comments/{id}

    public function newCommentsAction($slug)
    {} // "new_user_comments"   [GET] /users/{slug}/comments/new

    public function editCommentAction($slug, $id)
    {} // "edit_user_comment"   [GET] /users/{slug}/comments/{id}/edit

    public function removeCommentAction($slug, $id)
    {} // "remove_user_comment" [GET] /users/{slug}/comments/{id}/remove
}
```

Notice, we got rid of the ``User`` part in action names. That is because the RestBundle routing
already knows, that ``CommentsController::...`` is child resources of ``UsersController::getUser()``
resource.

## Include resource collections in application routing

Last step is mapping of your collection routes into the application ``routing.yml``:

```yaml
# app/config/routing.yml
users:
    type:     rest
    resource: "@AcmeHelloBundle/Resources/config/users_routes.yml"
```

That's all. Note that it's important to use the ``type: rest`` param when including your application's
routing file. Without it, rest routes will still work but resource collections will fail. If you get an
exception that contains ...routing loader does not support given key: "parent"... then you are most likely missing
the ``type: rest`` param in your application level routes include.

## Routes naming

RestBundle uses REST paths to generate route name. This means, that URL:

    [POST] /users/{slug}/comments/{id}/vote

will become the route with the name ``post_user_comment_vote``.

For further examples, see comments of controllers in the code above.

### Naming collisions

Sometimes, routes auto-naming will lead to route names collisions, so RestBundle route
collections provides a ``name_prefix`` (``name-prefix`` for xml and ``@NamePrefix`` for
annotations) parameter (you can use ``name_prefix`` only in a file loaded by the rest loader.):

```yaml
# app/config/routing.yml
users:
    type: rest  # Required for `RestYamlLoader` to process imported routes
    prefix: /api
    resource: "@AcmeHelloBundle/Resources/config/users_routes.yml"
```

```yaml
# src/Acme/HelloBundle/Resources/config/users_routes.yml
comments:
    type:         rest
    resource:     "@AcmeHelloBundle\Controller\CommentsController"
    name_prefix:  api_ # Our precious parameter
```

With this configuration, route name would become:

    api_vote_user_comment


Say NO to name collisions!

## That was it!
[Return to the index](index.md) or continue reading about [Manual definition of routes](7-manual-route-definition.md).

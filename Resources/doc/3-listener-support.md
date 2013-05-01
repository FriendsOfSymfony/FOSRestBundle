Step 3: Listener support
========================

[Listeners](http://symfony.com/doc/master/cookbook/service_container/event_listener.html)
are a way to hook into the request handling. This Bundle provides various events
from decoding the request content in the request (body listener), determining the
correct response format (format listener), reading parameters from the request
(parameter fetcher listener), to formatting the response either with a template engine
like twig or to f.e. xml or json using a serializer (view response listener)) as well
as automatically setting the accepted http methods in the response (accept listener).

With this in mind we now turn to explain each one of them.

All listeners except the ``mime_type`` one are disabled by default.  You can
enable one or more of these listeners.  For example, below you can see how to
enable all listeners:

```yaml
# app/config/config.yml
fos_rest:
    param_fetcher_listener: true
    body_listener: true
    format_listener: true
    view:
        view_response_listener: 'force'
```

### View Response listener

The view response listener makes it possible to simply return a ``View``
instance from action controllers. The final output will then automatically be
processed via the listener by the ``fos_rest.view_handler`` service.

This requires adding the SensioFrameworkExtraBundle to your vendors:

http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html

Now inside a controller its possible to simply return a ``View`` instance.

```php
<?php

use FOS\RestBundle\View\View;

class UsersController
{
    public function getUsersAction()
    {
        $view = View::create();

        ...

        $view->setData($data);
        return $view;
    }
}
```

As this feature is heavily based on the SensioFrameworkBundle, the example can
further be simplified by using the various annotations supported by that
bundle. There is also one additional annotation called ``@View()`` which
extends from the ``@Template()`` annotation.

The ``@View()`` and ``@Template()`` annotations behave essentially the same
with a minor difference. When ``view_response_listener`` is set to ``true``
instead of ``force`` and ``@View()`` is not used, then rendering
will be delegated to SensioFrameworkExtraBundle.

Note that it is necessary to disable view annotations in
SensioFrameworkExtraBundle so that FOSRestBundle can take over the handling.

```yaml
# app/config/config.yml
fos_rest:
    view:
        view_response_listener: force

sensio_framework_extra:
    view:    { annotations: false }
    router:  { annotations: true }
```

```php
<?php

use FOS\RestBundle\Controller\Annotations\View;

class UsersController
{
    /**
     * @View()
     */
    public function getUsersAction()
    {
        ...

        return $data;
    }
}
```
If ``@View()`` is used, the template variable name used to render templating
formats can be configured (default  ``'data'``):

```php
<?php

/**
 * @View(templateVar="users")
 */
public function getUsersAction()
{
    //...
}
```

The status code of the view can also be configured:

```php
<?php

/**
 * @View(statusCode=204)
 */
public function deleteUserAction()
{
    //...
}
```

The groups for the serializer can be configured as follows:

```php
<?php

/**
 * @View(serializerGroups={"group1", "group2"})
 */
public function getUsersAction()
{
    //...
}
```

See the following example code for more details:
https://github.com/liip/LiipHelloBundle/blob/master/Controller/ExtraController.php

### Body listener

The Request body decoding listener makes it possible to decode the contents of
a request in order to populate the "request" parameter bag of the Request. This
for example allows to receive data that normally would be sent via POST as
``application/x-www-form-urlencode`` in a different format (for example
application/json) in a PUT.

You can add a decoder for a custom format. You can also replace the default
decoder services provided by the bundle for the ``json`` and ``xml`` formats.
Below you can see how to override the decoder for the json format (the xml
decoder is explicitly kept to its default service):

```yaml
# app/config/config.yml
fos_rest:
    body_listener:
        decoders:
            json: acme.decoder.json
            xml: fos_rest.decoder.xml
```

Your custom decoder service must use a class that implements the
``FOS\Rest\Decoder\DecoderInterface``.

If you want to be able to use form with checkbox and have true and false value (without any issue) you have to use : fos_rest.decoder.jsontoform (available since fosrest 0.8.0)

### Format listener

The Request format listener attempts to determine the best format for the
request based on the Request's Accept-Header and the format priority
configuration. This way it becomes possible to leverage Accept-Headers to
determine the request format, rather than a file extension (like foo.json).

The ``default_priorities`` define the order of formats as the application
prefers.  The algorithm iteratively examines the provided Accept header first
looking at all the options with the highest ``q``. The first priority that
matches is returned. If none match the next lowest set of Accept headers with
equal ``q`` is examined and so on until there are no more Accept headers to
check. In this case ``fallback_format`` is used.

Note that if ``_format`` is matched inside the route, then a virtual Accept
header setting is added with a ``q`` setting one lower than the lowest Accept
header, meaning that format is checked for a match in the priorities last. If
``prefer_extension`` is set to ``true`` then the virtual Accept header will be
one higher than the highest ``q`` causing the extension to be checked first.

Note that setting ``default_priorities`` to a non empty array enables Accept
header negotiations, while adding '*/*' to the priorities will effectively
cause any priority to match.

```yaml
# app/config/config.yml
fos_rest:
    format_listener:
        default_priorities: ['json', html, '*/*']
        fallback_format: json
        prefer_extension: true
```

For example using the above configuration and the following Accept header:
```
text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json
```

And the following route:

```yaml
hello:
    pattern:  /foo.{_format}
    defaults: { _controller: foo.controller:indexAction, _format: ~ }
```

When calling:

* ``/foo`` will lead to setting the request format to ``json``
* ``/foo.html`` will lead to setting the request format to ``html``

Note that the format needs to either be supported by the ``Request`` class
natively or it needs to be added as documented here:
http://symfony.com/doc/current/cookbook/request/mime_type.html

### Mime type listener

This listener allows registering additional mime types in the ``Request``
class.  It works similar to the following cookbook entry:
http://symfony.com/doc/current/cookbook/request/mime_type.html


```yaml
# app/config/config.yml
fos_rest:
    view:
        mime_types: {'jsonp': ['application/javascript', 'application/javascript+jsonp']}
```

### Param fetcher listener

The param fetcher listener simply sets the ParamFetcher instance as a request attribute
configured for the matched controller so that the user does not need to do this manually.

```yaml
# app/config/config.yml
fos_rest:
    param_fetcher_listener: true
```

```php
<?php

use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;

class FooController extends Controller
{
    /**
     * Will look for a page query parameter, ie. ?page=XX
     * If not passed it will be automatically be set to the default of "1"
     * If passed but doesn't match the requirement "\d+" it will be also be set to the default of "1"
     * Note that if the value matches the default then no validation is run.
     * So make sure the default value really matches your expectations.
     *
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     *
     * In some case you also want to have a strict requirements but accept a null value, this is possible
     * thanks to the nullable option.
     * If ?count= parameter is set, the requirements will be checked strictly, if not, the null value will be used.
     * If you set the strict parameter without a nullable option, this will result in an error if the parameter is
     * missing from the query.
     *
     * @QueryParam(name="count", requirements="\d+", strict=true, nullable=true, description="Item count limit")
     *
     * Will look for a firstname request parameters, ie. firstname=foo in POST data.
     * If not passed it will error out when read out of the ParamFetcher since RequestParam defaults to strict=true
     * If passed but doesn't match the requirement "\d+" it will also error out (400 Bad Request)
     * Note that if the value matches the default then no validation is run.
     * So make sure the default value really matches your expectations.
     *
     * @RequestParam(name="firstname", requirements="[a-z]+", description="Firstname.")
     *
     * If you want to work with array: ie. ?ids[]=1&ids[]=2&ids[]=1337, use:
     *
     * @QueryParam(array="true", name="ids", requirements="\d+", default="1", description="List of ids")
     * (works with QueryParam and RequestParam)
     *
     * It will validate each entries of ids with your requirement, by this way, if an entry is invalid,
     * this one will be replaced by default value.
     *
     * ie: ?ids[]=1337&ids[]=notinteger will return array(1337, 1);
     * If ids is not defined, array(1) will be given
     *
     * Array must have a single depth or it will return default value. It's difficult to validate with
     * preg_match each deeps of array, if you want to deal with that, use another validation system.
     *
     * @param ParamFetcher $paramFetcher
     */
    public function getArticlesAction(ParamFetcher $paramFetcher)
    {
        $page = $paramFetcher->get('page');
        $articles = array('bim', 'bam', 'bingo');

        return array('articles' => $articles, 'page' => $page);
    }
```

Note: There is also ``$paramFetcher->all()`` to fetch all configured query parameters at once. And also
both ``$paramFetcher->get()`` and ``$paramFetcher->all()`` support and optional ``$strict`` parameter
to throw a ``\RuntimeException`` on a validation error.

Optionally the listener can also already set all configured query parameters as request attributes

```yaml
# app/config/config.yml
fos_rest:
    param_fetcher_listener: force
```

```php
<?php

class FooController extends Controller
{
    /**
     * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
     *
     * @param string $page
     */
    public function getArticlesAction($page)
    {
        $articles = array('bim', 'bam', 'bingo');

        return array('articles' => $articles, 'page' => $page);
    }
```

### Allowed Http Methods Listener

This listener add the ``Allow`` HTTP header to each request appending all allowed methods for a given resource.

Let's say we have the following routes:
```
api_get_users
api_post_users
api_get_user
```

A ``GET`` request to ``api_get_users`` will response in:

```
< HTTP/1.0 200 OK
< Date: Sat, 16 Jun 2012 15:17:22 GMT
< Server: Apache/2.2.22 (Ubuntu)
< allow: GET, POST
```

You need to enable this listener like this as it is disabled by default:

```
fos_rest:
    allowed_methods_listener: true
```

### Security Exception Listener

By default it is the responsibility of firewall access points to deal with AccessDeniedExceptions.
For example the ``form`` entry point will redirect to the login page. However for a RESTful application
proper response HTTP status codes should be provided. This listener is triggered before the normal exception listener
and firewall entry points and forces returning either a 403 or 401 status code for any of the formats configured.

It will return 401 for `Symfony\Component\Security\Core\Exception\AuthenticationException` or 403 for
`Symfony\Component\Security\Core\Exception\AccessDeniedException`.

You need to enable this listener like this as it is disabled by default:

```
fos_rest:
    access_denied_listener:
        # all requests using the 'json' format will return a 403 on an access denied violation
        json: true
```

It is also recommended to enable the exception controller described in the next chapter.

## That was it!
[Return to the index](index.md) or continue reading about [ExceptionController support](4-exception-controller-support.md).

Step 3: Listener support
========================

`Listeners`_ are a way to hook into the request handling. This Bundle provides
various events from decoding the request content in the request (body listener),
determining the correct response format (format listener), reading parameters
from the request (parameter fetcher listener), to formatting the response either
with a template engine like twig or to f.e. xml or json using a serializer (view
response listener)) as well as automatically setting the accepted HTTP methods
in the response (accept listener).

With this in mind we now turn to explain each one of them.

All listeners except the ``mime_type`` one are disabled by default. You can
enable one or more of these listeners.  For example, below you can see how to
enable all listeners:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        param_fetcher_listener: true
        body_listener: true
        format_listener: true
        view:
            view_response_listener: 'force'

View Response listener
----------------------

The view response listener makes it possible to simply return a ``View``
instance from action controllers. The final output will then automatically be
processed via the listener by the ``fos_rest.view_handler`` service.

This requires adding the `SensioFrameworkExtraBundle`_ to your vendors.

Now inside a controller its possible to simply return a ``View`` instance.

.. code-block:: php

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

As this feature is heavily based on the `SensioFrameworkExtraBundle`_, the
example can further be simplified by using the various annotations supported by
that bundle. There is also one additional annotation called ``@View()`` which
extends from the ``@Template()`` annotation.

The ``@View()`` and ``@Template()`` annotations behave essentially the same with
a minor difference. When ``view_response_listener`` is set to ``true`` instead
of ``force`` and ``@View()`` is not used, then rendering will be delegated to
`SensioFrameworkExtraBundle`_.

Note that it is necessary to disable view annotations in
`SensioFrameworkExtraBundle`_ so that FOSRestBundle can take over the handling.
However FOSRestBundle will do this automatically but it does not override any
explicit configuration. So make sure to remove or disable the following setting:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        view:
            view_response_listener: force

    sensio_framework_extra:
        view:    { annotations: false }

.. code-block:: php

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

If ``@View()`` is used, the template variable name used to render templating
formats can be configured (default  ``'data'``):

.. code-block:: php

    <?php

    /**
     * @View(templateVar="users")
     */
    public function getUsersAction()
    {
        //...
    }

The status code of the view can also be configured:

.. code-block:: php

    <?php

    /**
     * @View(statusCode=204)
     */
    public function deleteUserAction()
    {
        //...
    }

The groups for the serializer can be configured as follows:

.. code-block:: php

    <?php

    /**
     * @View(serializerGroups={"group1", "group2"})
     */
    public function getUsersAction()
    {
        //...
    }

Enabling the MaxDepth exclusion strategy support for the serializer can be
configured as follows:

.. code-block:: php

    <?php

    /**
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function getUsersAction()
    {
        //...
    }

See `this example code`_ for more details.

The ViewResponse listener will automatically populate your view with request
attributes if you do not provide any data when returning a view object. This
behaviour comes from `SensioFrameworkExtraBundle`_ and will automatically add
any variables listed in the ``_template_default_vars`` request attribute when no
data is supplied. In some cases, this is not desirable and can be disabled by
either supplying the data you want or disabling the automatic population of data
with the ``@View`` annotation:

.. code-block:: php

    /**
     * $user will no longer end up in the View's data.
     *
     * @View(populateDefaultVars=false)
     */
    public function getUserDetails(User $user)
    {
    }

Body listener
-------------

The Request body listener makes it possible to decode the contents of a request
in order to populate the "request" parameter bag of the Request. This for
example allows to receive data that normally would be sent via POST as
``application/x-www-form-urlencode`` in a different format (for example
application/json) in a PUT.

Decoders
~~~~~~~~

You can add a decoder for a custom format. You can also replace the default
decoder services provided by the bundle for the ``json`` and ``xml`` formats.
Below you can see how to override the decoder for the json format (the xml
decoder is explicitly kept to its default service):

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_listener:
            decoders:
                json: acme.decoder.json
                xml: fos_rest.decoder.xml

Your custom decoder service must use a class that implements the
``FOS\RestBundle\Decoder\DecoderInterface``.

If you want to be able to use form with checkbox and have true and false value
(without any issue) you have to use: ``fos_rest.decoder.jsontoform`` (available
since fosrest 0.8.0)

If the listener receives content that it tries to decode but the decode fails
then a BadRequestHttpException will be thrown with the message: ``'Invalid ' .
$format . ' message received'``. When combined with the :doc:`exception controller
support <4-exception-controller-support>` this means your API will provide
useful error messages to your API users if they are making invalid requests.

Array Normalizer
~~~~~~~~~~~~~~~~

Array Normalizers allow to transform the data after it has been decoded in order
to facilitate its processing.

For example, you may want your API's clients to be able to send requests with
underscored keys but if you use a decoder without a normalizer, you will receive
the data as it is and it can lead to incorrect mapping if you submit the request
directly to a Form. If you wish the body listener to transform underscored keys
to camel cased ones, you can use the ``camel_keys`` array normalizer:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_listener:
            array_normalizer: fos_rest.normalizer.camel_keys

Sometimes an array contains a key, which once normalized, will override an
existing array key. For example ``foo_bar`` and ``foo_Bar`` will both lead to
``fooBar``. If the normalizer receives this data, the listener will throw a
BadRequestHttpException with the message ``The key "foo_Bar" is invalid as it
will override the existing key "fooBar"``.

NB: If you use the ``camel_keys`` normalizer, you must be careful when choosing
your Form name.

You can also create your own array normalizer by implementing the
``FOS\RestBundle\Normalizer\ArrayNormalizerInterface``.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_listener:
            array_normalizer: acme.normalizer.custom

By default, the array normalizer is only applied to requests with a decodable format.
If you want form data to be normalized, you can use the ``forms`` flag:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_listener:
            array_normalizer:
                service: fos_rest.normalizer.camel_keys
                forms: true

Request Body Converter Listener
-------------------------------

`ParamConverters`_ are a way to populate objects and inject them as controller
method arguments. The Request body converter makes it possible to deserialize
the request body into an object.

This converter requires that you have installed `SensioFrameworkExtraBundle`_
and have the converters enabled:

.. code-block:: yaml

    # app/config/config.yml
    sensio_framework_extra:
        request: { converters: true }

To enable the Request body converter, add the following configuration:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_converter:
            enabled: true

.. note::

    You will probably want to disable the automatic route generation
    (``@NoRoute``) for routes using the body converter, and instead define the
    routes manually to avoid having the deserialized, typehinted objects
    (``$post`` in this example) appear in the route as a parameter.

Now, in the following example, the request body will be deserialized into a new
instance of ``Post`` and injected into the ``$post`` variable:

.. code-block:: php

    use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

    // ...

    /**
     * @ParamConverter("post", converter="fos_rest.request_body")
     */
    public function putPostAction(Post $post)
    {
        // ...
    }

You can configure the context used by the serializer during deserialization
via the ``deserializationContext`` option:

.. code-block:: php

    /**
     * @ParamConverter("post", converter="fos_rest.request_body", options={"deserializationContext"={"groups"={"group1", "group2"}, "version"="1.0"}})
     */
    public function putPostAction(Post $post)
    {
        // ...
    }

Validation
~~~~~~~~~~

If you would like to validate the deserialized object, you can do so by
enabling validation:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_converter:
            enabled: true
            validate: true
            validation_errors_argument: validationErrors # This is the default value

The validation errors will be set on the ``validationErrors`` controller argument:

.. code-block:: php

    /**
     * @ParamConverter("post", converter="fos_rest.request_body")
     */
    public function putPostAction(Post $post, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            // Handle validation errors
        }

        // ...
    }

Format listener
---------------

The Request format listener attempts to determine the best format for the
request based on the Request's Accept-Header and the format priority
configuration. This way it becomes possible to leverage Accept-Headers to
determine the request format, rather than a file extension (like foo.json).

The ``priorities`` define the order of media types as the application
prefers. Note that if a format is provided instead of a media type, the
format is converted into a list of media types matching the format.
The algorithm iteratively examines the provided Accept header first
looking at all the options with the highest ``q``. The first priority that
matches is returned. If none match the next lowest set of Accept headers with
equal ``q`` is examined and so on until there are no more Accept headers to
check. In this case ``fallback_format`` is used.

Note that if ``_format`` is matched inside the route, then a virtual Accept
header setting is added with a ``q`` setting one lower than the lowest Accept
header, meaning that format is checked for a match in the priorities last. If
``prefer_extension`` is set to ``true`` then the virtual Accept header will be
one higher than the highest ``q`` causing the extension to be checked first.
Setting ``priorities`` to a non empty array enables Accept header negotiations.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        format_listener:
            rules:
                # setting fallback_format to json means that instead of considering the next rule in case of a priority mismatch, json will be used
                - { path: '^/', host: 'api.%domain%', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: false }
                # setting fallback_format to false means that instead of considering the next rule in case of a priority mismatch, a 406 will be caused
                - { path: '^/image', priorities: ['jpeg', 'gif'], fallback_format: false, prefer_extension: true }
                # setting fallback_format to null means that in case of a priority mismatch the next rule will be considered
                - { path: '^/admin', methods: [ 'GET', 'POST'], priorities: [ 'xml', 'html'], fallback_format: ~, prefer_extension: false }
                # setting fallback_format to null, while setting exception_fallback_format to xml, will mean that in case of an exception, xml will be used
                - { path: '^/api', priorities: [ 'xml', 'json'], fallback_format: ~, exception_fallback_format: xml, prefer_extension: false }
                # setting a priority to */* basically means any format will be matched
                - { path: '^/', priorities: [ 'text/html', '*/*'], fallback_format: html, prefer_extension: true }

For example using the above configuration and the following Accept header:

.. code-block:: text

    text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8,application/json

And the following route:

.. code-block:: yaml

    hello:
        pattern:  /foo.{_format}
        defaults: { _controller: foo.controller:indexAction, _format: ~ }

When calling:

* ``/foo.json`` will lead to setting the request format to ``json``
* ``/foo`` will lead to setting the request format to ``html``

Furthermore the listener sets a ``media_type`` attribute on the request in
case the listener is configured with a ``MediaTypeNegotiatorInterface`` instance,
which is the case by default, with the matched media type.

.. code-block:: php

    // f.e. text/html or application/vnd.custom_something+json etc.
    $mediaType = $request->attributes->get('media_type');

Note take care to configure the ``priorities`` carefully especially when the
controller actions for specific routes only handle necessary security checks
for specific formats. In such cases it might make sense to hard code the format
in the controller action.

.. code-block:: php

    public function getAction(Request $request)
    {
        $view = new View();
        // hard code the output format of the controller action
        $view->setFormat('html');

        // ...
    }

Note that the format needs to either be supported by the ``Request`` class
natively or it needs to be added as documented here or using the
`mime type listener`_ explained in the Symfony documentation.

Disabling the Format Listener via Rules
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Often when integrating this Bundle with existing applications, it might be
useful to disable the format listener for some routes. In this case it is
possible to define a rule that will stop the format listener from determining a
format by setting ``stop`` to ``true`` as a rule option. Any rule containing
this setting and any rule following will not be considered and the Request
format will remain unchanged.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        format_listener:
            rules:
                - { path: '^/api', priorities: ['json', 'xml'], fallback_format: json, prefer_extension: false }
                - { path: '^/', stop: true } # Available for version >= 1.5

Media Type Version Extraction
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The format listener can also determine the version of the selected media type
based on a regular expression. The regular expression can be configured as
follows. Setting it to an empty value will disable the behavior entirely.

.. code-block:: yaml

    fos_rest:
        format_listener:
            media_type:
                version_regex:        '/(v|version)=(?P<version>[0-9\.]+)/'

The matched version is set as a Request attribute with the name ``version``,
and when using JMS serializer it is also set as an exclusion strategy
automatically in the ``ViewHandler``.

See `this JMS Serializer article`_ for details.

Mime type listener
------------------

This listener allows registering additional mime types in the ``Request``
class. It works similar to the `mime type listener`_ proposed by Symfony.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        view:
            mime_types: {'jsonp': ['application/javascript+jsonp']}

Param fetcher listener
----------------------

The param fetcher listener simply sets the ParamFetcher instance as a request attribute
configured for the matched controller so that the user does not need to do this manually.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        param_fetcher_listener: true

.. code-block:: php

    <?php

    use FOS\RestBundle\Request\ParamFetcher;
    use FOS\RestBundle\Controller\Annotations\RequestParam;
    use FOS\RestBundle\Controller\Annotations\QueryParam;
    use Acme\FooBundle\Validation\Constraints\MyComplexConstraint

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
         * Will check if a blank value, e.g an empty string is passed and if so, it will set to the default of asc.
         *
         * @QueryParam(name="sort", requirements="(asc|desc)+", allowBlank=false, default="asc" description="Sort direction")
         *
         * Will look for a firstname request parameters, ie. firstname=foo in POST data.
         * If not passed it will error out when read out of the ParamFetcher since RequestParam defaults to strict=true
         * If passed but doesn't match the requirement "[a-z]+" it will also error out (400 Bad Request)
         * Note that if the value matches the default then no validation is run.
         * So make sure the default value really matches your expectations.
         *
         * @RequestParam(name="firstname", requirements="[a-z]+", description="Firstname.")
         *
         * If you want to work with array: ie. ?ids[]=1&ids[]=2&ids[]=1337, use:
         *
         * @QueryParam(array=true, name="ids", requirements="\d+", default="1", description="List of ids")
         * (works with QueryParam and RequestParam)
         *
         * It will validate each entries of ids with your requirement, by this way, if an entry is invalid,
         * this one will be replaced by default value.
         *
         * ie: ?ids[]=1337&ids[]=notinteger will return array(1337, 1);
         * If ids is not defined, array(1) will be given
         *
         * Array must have a single depth or it will return default value. It's difficult to validate with
         * preg_match each deeps of array, if you want to deal with that, you can use a constraint:
         *
         * @QueryParam(array=true, name="filters", requirements=@MyComplexConstraint, description="List of complex filters")
         *
         * In this example, the ParamFetcher will validate each value of the array with the constraint, returning the
         * default value if you are in safe mode or throw a BadRequestHttpResponse containing the constraint violation
         * messages in the message.
         *
         * @param ParamFetcher $paramFetcher
         */
        public function getArticlesAction(ParamFetcher $paramFetcher)
        {
            // ParamFetcher params can be dynamically added during runtime instead of only compile time annotations.
            $dynamicRequestParam = new RequestParam();
            $dynamicRequestParam->name = "dynamic_request";
            $dynamicRequestParam->requirements = "\d+";
            $paramFetcher->addParam($dynamicRequestParam);

            $dynamicQueryParam = new QueryParam();
            $dynamicQueryParam->name = "dynamic_query";
            $dynamicQueryParam->requirements="[a-z]+";
            $paramFetcher->addParam($dynamicQueryParam);

            $page = $paramFetcher->get('page');
            $articles = array('bim', 'bam', 'bingo');

            return array('articles' => $articles, 'page' => $page);
        }

.. note::

    There is also ``$paramFetcher->all()`` to fetch all configured query
    parameters at once. And also both ``$paramFetcher->get()`` and
    ``$paramFetcher->all()`` support and optional ``$strict`` parameter to throw
    a ``\RuntimeException`` on a validation error.

.. note::

    The ParamFetcher requirements feature requires the symfony/validator
    component.

Optionally the listener can also already set all configured query parameters as
request attributes

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        param_fetcher_listener: force

.. code-block:: php

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

Allowed Http Methods Listener
-----------------------------

This listener add the ``Allow`` HTTP header to each request appending all
allowed methods for a given resource.

Let's say we have the following routes:

.. code-block:: text

    api_get_users
    api_post_users
    api_get_user

A ``GET`` request to ``api_get_users`` will response in:

.. code-block:: text

    HTTP/1.0 200 OK
    Date: Sat, 16 Jun 2012 15:17:22 GMT
    Server: Apache/2.2.22 (Ubuntu)
    allow: GET, POST

You need to enable this listener like this as it is disabled by default:

.. code-block:: yaml

    fos_rest:
        allowed_methods_listener: true

Security Exception Listener
---------------------------

By default it is the responsibility of firewall access points to deal with
AccessDeniedExceptions. For example the ``form`` entry point will redirect to
the login page. However for a RESTful application proper response HTTP status
codes should be provided. This listener is triggered before the normal exception
listener and firewall entry points and forces returning either a 403 or 401
status code for any of the formats configured.

It will return 401 for
``Symfony\Component\Security\Core\Exception\AuthenticationException`` or 403 for
``Symfony\Component\Security\Core\Exception\AccessDeniedException``.

As a 401-response requires an authentication-challenge, you can set one using
the configuration ``unauthorized_challenge`` or leave it blank if you don't want
to send a challenge in the ``WWW-Authenticate`` header to the client.

If you want to use an advanced value in this header, it's worth looking at this:
`Test Cases for HTTP Test Cases for the HTTP WWW-Authenticate header field`_.

You need to enable this listener like this as it is disabled by default:

.. code-block: yaml

    fos_rest:
        unauthorized_challenge: "Basic realm=\"Restricted Area\""
        access_denied_listener:
            # all requests using the 'json' format will return a 403 on an access denied violation
            json: true

It is also recommended to enable the exception controller described in the next chapter.

Priorities
----------

==========================  =====================  ========
Listener                    Event                  Priority
==========================  =====================  ========
``MimeTypeListener``        ``kernel.request``     200
``FormatListener``          ``kernel.request``     34
``VersionListener``         ``kernel.request``     33
``BodyListener``            ``kernel.request``     10
``ParamFetcherListener``    ``kernel.controller``  5
``ViewResponseListener``    ``kernel.controller``  -10
``ViewResponseListener``    ``kernel.view``        100
``AllowedMethodsListener``  ``kernel.response``    0
==========================  =====================  ========

That was it!

.. _`Listeners`: http://symfony.com/doc/master/cookbook/service_container/event_listener.html
.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`this example code`: https://github.com/liip/LiipHelloBundle/blob/master/Controller/ExtraController.php
.. _`ParamConverters`: http://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`mime type listener`: http://symfony.com/doc/current/cookbook/request/mime_type.html
.. _`this JMS Serializer article`: http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#versioning-objects
.. _`Test Cases for HTTP Test Cases for the HTTP WWW-Authenticate header field`: http://greenbytes.de/tech/tc/httpauth/

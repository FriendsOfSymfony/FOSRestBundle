Param Fetcher Listener
======================

The param fetcher listener simply sets the ParamFetcher instance as a request attribute
configured for the matched controller so that the user does not need to do this manually.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        param_fetcher_listener: true

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Request\ParamFetcher;
    use FOS\RestBundle\Controller\Annotations\RequestParam;
    use FOS\RestBundle\Controller\Annotations\QueryParam;
    use FOS\RestBundle\Controller\Annotations\FileParam;
    use Acme\FooBundle\Validation\Constraints\MyComplexConstraint;

    class FooController extends Controller
    {
        /**
         * @QueryParam(name="page", requirements="\d+", default="1", description="Page of the overview.")
         * Will look for a page query parameter, ie. ?page=XX
         * If not passed it will be automatically be set to the default of "1"
         * If passed but doesn't match the requirement "\d+" it will be also be set to the default of "1"
         * Note that if the value matches the default then no validation is run.
         * So make sure the default value really matches your expectations.
         *
         * @QueryParam(name="count", requirements="\d+", strict=true, nullable=true, description="Item count limit")
         * In some case you also want to have a strict requirements but accept a null value, this is possible
         * thanks to the nullable option.
         * If ?count= parameter is set, the requirements will be checked strictly, if not, the null value will be used.
         * If you set the strict parameter without a nullable option, this will result in an error if the parameter is
         * missing from the query.
         *
         * @QueryParam(name="sort", requirements="(asc|desc)", allowBlank=false, default="asc", description="Sort direction")
         * Will check if a blank value, e.g an empty string is passed and if so, it will set to the default of asc.
         *
         * @RequestParam(name="firstname", requirements="[a-z]+", description="Firstname.")
         * Will look for a firstname request parameters, ie. firstname=foo in POST data.
         * If not passed it will error out when read out of the ParamFetcher since RequestParam defaults to strict=true
         * If passed but doesn't match the requirement "[a-z]+" it will also error out (400 Bad Request)
         * Note that if the value matches the default then no validation is run.
         * So make sure the default value really matches your expectations.
         *
         * @RequestParam(name="search", requirements="[a-z]+", description="search")
         * @RequestParam(name="byauthor", requirements="[a-z]+", description="by author", incompatibles={"search"})
         * Imagine you have an api for a blog with to get Articles with two ways of filtering
         *   1 by filtering the text
         *   2 by filtering the author
         * and you don't have yet implemented the possibility to filter by both at the same time.
         * In order to prevent clients from doing a request with both (which will produce not the expected
         * resut and is likely to be considered as a bug) you can precise the parameters can't be present
         * at the same time by doing
         *
         * @QueryParam(map=true, name="ids", requirements="\d+", default="1", description="List of ids")
         * If you want to map the value as an array (apply the requirements to each element): ie. ?ids[]=1&ids[]=2&ids[]=1337.
         * (works with QueryParam and RequestParam)
         *
         * It will validate each entries of ids with your requirement, by this way, if an entry is invalid,
         * this one will be replaced by default value.
         *
         * ie: ?ids[]=1337&ids[]=notinteger will return array(1337, 1);
         * If ids is not defined, array(1) will be given
         *
         * Array must have a single depth if you use a regex. It's difficult to validate with
         * preg_match each deeps of array, if you want to deal with that, you can use a constraint:
         *
         * @QueryParam(map=true, name="filters", requirements=@MyComplexConstraint, description="List of complex filters")
         * In this example, the ParamFetcher will validate each value of the array with the constraint, returning the
         * default value if you are in safe mode or throw a BadRequestHttpResponse containing the constraint violation
         * messages in the message.
         *
         * @FileParam(name="fooFile", incompatibles={"firstName"})
         * In this example, if firstName and fooFile are defined at the time,
         * ParamFetcher::get("fooFile") will throw an error.
         * Otherwise, if the file is valid, it will return a Symfony\Component\HttpFoundation\File\UploadedFile.
         * See http://api.symfony.com/2.7/Symfony/Component/HttpFoundation/File/UploadedFile.html
         *
         * @FileParam(name="myJsonFile", requirements={"mimeTypes"="application/json", "maxSize"="2k"}, strict=true)
         * The ParamFetcher will throw an error if the file passed is not a json file or is bigger than 2,000 bytes.
         * See all file possible requirements: http://symfony.com/doc/current/reference/constraints/File.html
         *
         * @FileParam(name="avatar", requirements={"mimeTypes"="image/jpeg", "minWidth"="200"}, image=true)
         * The ParamFetcher will throw an error if the file passed is not an image and is not larger than 200px.
         * See all image possible requirements: http://symfony.com/doc/current/reference/constraints/Image.html
         *
         * @FileParam(name="identityCard", image=true, default="noPicture")
         * If "identityCard" is not an image, paramFetcher will return "noPicture"
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

    namespace AppBundle\Controller;

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

Container parameters can be used in requirements and default field.

.. note::

    The percent sign (%) in ``requirements`` and ``default`` field, must be
    escaped with another percent sign


.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    class FooController extends Controller
    {
        /**
         * Use the "locale" parameter as the default value
         * @QueryParam(name="language", default="%locale%")
         *
         * The "baz" container parameter is used here as requirements
         * Can be used for complex or auto-generated regex
         * @QueryParam(name="foo", requirements="%baz%")
         *
         * The percent sign must be escaped
         * @QueryParam(name="val", default="75 %%")
         */
        public function getArticlesAction(ParamFetcher $paramFetcher)
        {
            ...
        }

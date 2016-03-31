Step 4: ExceptionController support
===================================

When implementing an API it is also necessary to handle exceptions in a RESTful
way, while ensuring that no security sensitive information leaks out. This
bundle provides an extra controller for that job. Using this custom
ExceptionController it is possible to leverage the View layer when building
responses for uncaught Exceptions.

The ExceptionController can be enabled via the FOSRestBundle
configuration:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        exception:
            enabled: true
            exception_controller: 'Acme\DemoBundle\Controller\ExceptionController::showAction'

.. note::

    The FOSRestBundle ExceptionController is executed before the one of the TwigBundle.

.. note::

    FOSRestBundle defines two services for exception rendering, by default it
    configures ``fos_rest.exception.controller`` which only supports rendering
    via a serializer. In case no explicit controller is configured by the user
    and TwigBundle is detected it will automatically configure
    ``fos_rest.exception.twig_controller`` which additionally also supports
    rendering via Twig.

To map Exception classes to HTTP response status codes an *exception map* may
be configured, where the keys match a fully qualified class name and the values
are either an integer HTTP response status code or a string matching a class
constant of the ``Symfony\Component\HttpFoundation\Response`` class:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        exception:
            codes:
                'Symfony\Component\Routing\Exception\ResourceNotFoundException': 404
                'Doctrine\ORM\OptimisticLockException': HTTP_CONFLICT
            messages:
                'Acme\HelloBundle\Exception\MyExceptionWithASafeMessage': true

If you want to display the message from the exception in the content of the
response, add the exception to the messages map as well. If not only the status
code will be returned.

If you know what status code you want to return you do not have to add a
mapping, you can do this in your controller:

.. code-block:: php

    <?php
    class UsersController extends Controller
    {
        public function postUserCommentsAction($slug)
        {
            if (!$this->validate($slug)) {
                throw new HttpException(400, "New comment is not valid.");
            }
        }
    }

In order to make the serialization format of exceptions customizable it is possible to
use serializer normalizers.

See `how to create handlers`_ for the JMS serializer and `how to create normalizers`_ for the Symfony serializer.

That was it!

.. note::

    If you are receiving a 500 error where you would expect a different response, the issue
    is likely caused by an exception inside the ExceptionController. For example a template
    is not found or the serializer failed. You should take a look at the logs of your app to see if an uncaught exception has been logged.

.. _`how to create handlers`: http://jmsyst.com/libs/serializer/master/handlers
.. _`how to create normalizers`: http://thomas.jarrand.fr/blog/serialization/

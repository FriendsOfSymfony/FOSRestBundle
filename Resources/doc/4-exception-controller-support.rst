Step 4: ExceptionController support
===================================

When implementing an API it is also necessary to handle exceptions in a RESTful
way, while ensuring that no security sensitive information leaks out. This
bundle provides an extra controller for that job. Using this custom
ExceptionController it is possible to leverage the View layer when building
responses for uncaught Exceptions.

The ExceptionController can be enabled either via the FOSRestBundle
configuration and optionally an explicit controller action can be configured as
well:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        exception:
            enabled: true
            exception_controller: 'Acme\DemoBundle\Controller\ExceptionController::showAction'

Alternatively the TwigBundle configuration can be used to enable the ExceptionController:

.. code-block:: yaml

    # app/config/config.yml
    twig:
        exception_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'

When enabling the RestBundle view-layer-aware ExceptionController it automatically
disables the TwigBundle exception listener and subsequent configuration.

To map Exception classes to HTTP response status codes an *exception map* may
be configured, where the keys match a fully qualified class name and the values
are either an integer HTTP response status code or a string matching a class
constant of the ``FOS\RestBundle\Util\Codes`` class:

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
configure a ``exception_handler``. Users of JMS serializer can further customize the output
by setting a custom ``exception_wrapper_handler``.

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        service:
            exception_handler:    fos_rest.view.exception_wrapper_handler
        view:
            # only relevant when using the JMS serializer for serialization
            exception_wrapper_handler:  null


See `this example configuration`_ for more details.

That was it!

.. note::

    If you are receiving a 500 error where you would expect a different response, the issue
    is likely caused by an exception inside the ExceptionController. For example a template
    is not found or the serializer failed.

.. _`this example configuration`: https://github.com/liip-forks/symfony-standard/blob/techtalk/app/config/config.yml

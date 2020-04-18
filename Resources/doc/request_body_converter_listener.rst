Request Body Converter Listener
===============================

`ParamConverters`_ are a way to populate objects and inject them as controller
method arguments. The Request body converter makes it possible to deserialize
the request body into an object.

This converter requires that you have installed `SensioFrameworkExtraBundle`_
and have the converters enabled:

.. code-block:: yaml

    sensio_framework_extra:
        request: { converters: true }

To enable the Request body converter, add the following configuration:

.. code-block:: yaml

    fos_rest:
        body_converter:
            enabled: true

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

You can configure the validation groups used by the validator
via the ``validator`` option:

.. code-block:: php

    /**
     * @ParamConverter("post", converter="fos_rest.request_body", options={"validator"={"groups"={"foo", "bar"}}})
     */
    public function putPostAction(Post $post, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            // Handle validation errors
        }

        // ...
    }


.. _`ParamConverters`: http://symfony.com/doc/master/bundles/SensioFrameworkExtraBundle/annotations/converters.html
.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html

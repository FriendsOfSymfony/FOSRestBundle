Body Listener
=============

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

If you want to be able to use a checkbox within a form and have true and false
values (without any issue) you have to use: ``fos_rest.decoder.jsontoform``
(available since FosRestBundle 0.8.0)

If the listener receives content that it tries to decode but the decode fails
then a BadRequestHttpException will be thrown with the message: ``'Invalid ' .
$format . ' message received'``. When combined with the :doc:`exception controller
support <4-exception-controller-support>` this means your API will provide
useful error messages to your API users if they are making invalid requests.

Array Normalizer
~~~~~~~~~~~~~~~~

Array normalizers allow to transform the data after it has been decoded in order
to facilitate its processing.

For example, you may want your API's clients to be able to send requests with
underscored keys but if you use a decoder without a normalizer, you will receive
the data as it is and it can lead to incorrect mapping if you submit the request
directly to a form. If you wish the body listener to transform underscored keys
to camel cased ones, you can use the ``camel_keys`` array normalizer:

.. code-block:: yaml

    # app/config/config.yml
    fos_rest:
        body_listener:
            array_normalizer: fos_rest.normalizer.camel_keys

.. note::

    If you want to ignore leading underscores, for example in ``_username`` you can
    instead use the ``fos_rest.normalizer.camel_keys_with_leading_underscore`` service.

Sometimes an array contains a key, which once normalized, will override an
existing array key. For example ``foo_bar`` and ``foo_Bar`` will both lead to
``fooBar``. If the normalizer receives this data, the listener will throw a
BadRequestHttpException with the message ``The key "foo_Bar" is invalid as it
will override the existing key "fooBar"``.

.. note::

    If you use the ``camel_keys`` normalizer, you must be careful when choosing
    your form name.

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

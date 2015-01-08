Step 1: Setting up the bundle
=============================

A) Install FOSRestBundle
------------------------

.. note::

    This bundle recommends using `JMSSerializer`_ which is integrated into Symfony
    via `JMSSerializerBundle`_.

    If you want to use JMSSerializer, take a look into the instructions of the
    bundle to install it and set it up. You can also use `Symfony Serializer`_.
    But in this case, you need to manually set it up and configure FOSRestBundle
    to use it via the ``service`` section in the app config

Simply run assuming you have installed ``composer.phar`` or ``composer`` binary:

.. code-block:: bash

    $ composer require friendsofsymfony/rest-bundle

B) Enable the bundle
--------------------

Finally, enable the bundle in the kernel:

.. code-block:: php

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new FOS\RestBundle\FOSRestBundle(),

            // if you choose to use JMSSerializer, make sure that it is registered in your application

            // new JMS\SerializerBundle\JMSSerializerBundle(),
        );
    }

That was it!

.. _``JMSSerializer``: https://github.com/schmittjoh/serializer
.. _``JMSSerializerBundle``: https://github.com/schmittjoh/JMSSerializerBundle
.. _``Symfony Serializer``: https://github.com/symfony/Serializer

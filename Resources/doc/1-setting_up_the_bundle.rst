Step 1: Setting up the bundle
=============================

A) Download the Bundle
----------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require friendsofsymfony/rest-bundle "~1.4"

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

.. note::

    This bundle recommends using `JMSSerializer`_ which is integrated into Symfony
    via `JMSSerializerBundle`_.

    If you want to use JMSSerializer, take a look into the instructions of the
    bundle to install it and set it up. You can also use `Symfony Serializer`_.
    But in this case, you need to manually set it up and configure FOSRestBundle
    to use it via the ``service`` section in the app config

B) Enable the Bundle
--------------------

Then, enable the bundle by adding the following line in the ``app/AppKernel.php``
file of your project:

.. code-block:: php

    // app/AppKernel.php
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new FOS\RestBundle\FOSRestBundle(),
            );
        
            // ...
        }
    }

That was it!

.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md
.. _`JMSSerializer`: https://github.com/schmittjoh/serializer
.. _`JMSSerializerBundle`: https://github.com/schmittjoh/JMSSerializerBundle
.. _`Symfony Serializer`: https://github.com/symfony/Serializer

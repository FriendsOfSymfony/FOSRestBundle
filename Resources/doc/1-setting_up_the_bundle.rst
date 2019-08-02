Step 1: Setting up the bundle
=============================

A) Download the Bundle
----------------------

Assuming you already have `set up a Symfony project`_, you can add the FOSRestBundle to it. Open a command console,
enter your project directory and execute the following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require friendsofsymfony/rest-bundle

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

.. caution::

    If you are not using Flex, you also have to enable the bundle by adding the following line in the ``app/AppKernel.php``::
    
        // app/AppKernel.php
        class AppKernel extends Kernel
        {
            public function registerBundles()
            {
                $bundles = [
                    // ...
                    new FOS\RestBundle\FOSRestBundle(),
                ];

                // ...
            }
        }
    
B) Enable a Serializer
----------------------

This bundle needs a serializer to work correctly. In most cases,
you'll need to enable a serializer or install one. This bundle tries
the following (in the given order) to determine the serializer to use:

#. The one you configured using ``fos_rest.service.serializer`` (if you did).
#. The JMS serializer, if the `JMSSerializerBundle`_ is available (and registered).
#. The `Symfony Serializer`_ if it's enabled (or any service called ``serializer``).

That was it!

.. _`set up a Symfony project`: https://symfony.com/download
.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md
.. _`JMSSerializer`: https://github.com/schmittjoh/serializer
.. _`JMSSerializerBundle`: https://github.com/schmittjoh/JMSSerializerBundle
.. _`Symfony Serializer`: http://symfony.com/doc/current/cookbook/serializer.html

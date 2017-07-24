Getting Started With FOSRestBundle
==================================

`FOSRestBundle`_  is a tool to help you in the job of creating a REST API with
Symfony.

.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle

.. toctree::
    :hidden:

    2-the-view-layer
    empty-content-status-code
    3-listener-support
    view_response_listener
    body_listener
    request_body_converter_listener
    format_listener
    versioning
    param_fetcher_listener
    4-exception-controller-support
    5-automatic-route-generation_single-restful-controller
    6-automatic-route-generation_multiple-restful-controllers
    7-manual-route-definition
    annotations-reference
    configuration-reference

Installation
------------

Before using this bundle in your project, add it to your composer.json file:

.. code-block:: bash

    $ composer require friendsofsymfony/rest-bundle

Then, like for any other bundle, include it in your Kernel class::

    public function registerBundles()
    {
        $bundles = array(
            // ...

            new FOS\RestBundle\FOSRestBundle(),
        );

        // ...
    }

At last, make sure you have a serializer enabled. The bundle will look at these
three possibilities **in this order** to decide which serializer to use:

#. The service ``fos_rest.service.serializer`` if it is configured (must be an
instance of ``FOS\RestBundle\Serializer``).
#. The JMS serializer, if the `JMSSerializerBundle`_ is available (and registered).
#. The `Symfony Serializer`_

.. _`JMSSerializerBundle`: https://github.com/schmittjoh/JMSSerializerBundle
.. _`Symfony Serializer`: http://symfony.com/doc/current/cookbook/serializer.html

Bundle usage
------------

This bundle contains many features that are loosely coupled so you may or may
not need to use all of them.

FOSRestBundle provides several tools to assist you in building REST applications:

- :doc:`The view layer <2-the-view-layer>`, to create format agnostic controllers
- :doc:`Listener support <3-listener-support>`
- :doc:`ExceptionController support <4-exception-controller-support>`
- :doc:`Automatic route generation: single RESTful controller <5-automatic-route-generation_single-restful-controller>` (for simple resources)
- :doc:`Automatic route generation: multiple RESTful controllers <6-automatic-route-generation_multiple-restful-controllers>` (for resources with child/subresources)
- :doc:`Manual definition of routes <7-manual-route-definition>`

Config reference
----------------

- :doc:`Configuration reference <configuration-reference>` for a reference on
  the available configuration options
- :doc:`Annotations reference <annotations-reference>` for a reference on
  the available configurations through annotations

Example applications
--------------------

The following bundles/applications use the FOSRestBundle and can be used as a
guideline:

- The `LiipHelloBundle`_ provides several examples for the RestBundle.

- There is also `a fork of the Symfony2 Standard Edition`_ that is configured to
  show the LiipHelloBundle examples.

- The `FOSCommentBundle`_ uses FOSRestBundle for its API.

- The `Symfony2 Rest Edition`_ provides a complete example of how to build a
  controller that works for both HTML as well as JSON/XML.

.. _`LiipHelloBundle`: https://github.com/liip/LiipHelloBundle
.. _`a fork of the Symfony2 Standard Edition`: https://github.com/liip-forks/symfony-standard/tree/techtalk
.. _`FOSCommentBundle`: https://github.com/FriendsOfSymfony/FOSCommentBundle
.. _`Symfony2 Rest Edition`: https://github.com/gimler/symfony-rest-edition

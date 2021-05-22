Getting Started With FOSRestBundle
==================================

.. toctree::
    :hidden:

    1-setting_up_the_bundle
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
    annotations-reference

Installation
------------

Installation is a quick (I promise!) one-step process:

1. :doc:`Setting up the bundle <1-setting_up_the_bundle>`

Bundle usage
------------

Before you start using the bundle it is advised you run a quick look over the 
listed sections below. This bundle contains many features that are loosely
coupled so you may or may not need to use all of them. This bundle is just a
tool to help you in the job of creating a REST API with Symfony.

FOSRestBundle provides several tools to assist in building REST applications:

- :doc:`The view layer <2-the-view-layer>`
- :doc:`Listener support <3-listener-support>`
- :doc:`ExceptionController support <4-exception-controller-support>`

Config reference
----------------

- Run ``bin/console config:dump-reference fos_rest`` for a reference of
  the available configuration options
- :doc:`Annotations reference <annotations-reference>` for a reference on
  the available configurations through annotations

Example applications
--------------------

The following bundles/applications use the FOSRestBundle and can be used as a
guideline:

- The `FOSCommentBundle`_ uses FOSRestBundle for its API.

- The `Symfony2 Rest Edition`_ provides a complete example of how to build a
  controller that works for both HTML as well as JSON/XML.

.. _`FOSCommentBundle`: https://github.com/FriendsOfSymfony/FOSCommentBundle
.. _`Symfony2 Rest Edition`: https://github.com/gimler/symfony-rest-edition

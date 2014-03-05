Getting Started With FOSRestBundle
=====================================

## Installation

Installation is a quick (I promise!) 1 step process:

1. [Setting up the bundle](1-setting_up_the_bundle.md)

## Bundle usage

Before you start using the bundle it is advised you run a quick look over the 6 sections listed below.
This bundle contains many features that are loosely coupled so you may or may not need to use all of
them. This bundle is just a tool to help you in the job of creating a REST API with Symfony2.

FOSRestBundle provides several tools to assist in building REST applications:

- [The view layer](2-the-view-layer.md)
- [Listener support](3-listener-support.md)
- [ExceptionController support](4-exception-controller-support.md)
- [Automatic route generation: single RESTful controller](5-automatic-route-generation_single-restful-controller.md) (for simple resources)
- [Automatic route generation: multiple RESTful controllers](6-automatic-route-generation_multiple-restful-controllers.md) (for resources with child/subresources)
- [Manual definition of routes](7-manual-route-definition.md)

### Config reference

- [Configuration reference](configuration-reference.md) for a reference on the available configuration options
- [Annotations reference](annotations-reference.md) for a reference on on the available configurations through annotations

### Example application(s)

The following bundles/applications use the FOSRestBundle and can be used as a
guideline:

- The LiipHelloBundle provides several examples for the RestBundle:
  https://github.com/liip/LiipHelloBundle

- There is also a fork of the Symfony2 Standard Edition that is configured to
  show the LiipHelloBundle examples:
  https://github.com/liip-forks/symfony-standard/tree/techtalk

- The FOSCommentBundle uses FOSRestBundle for its api:
  https://github.com/FriendsOfSymfony/FOSCommentBundle

- The Symfony2 Rest Edition provides a complete example of how to build a 
  controller that works for both HTML as well as JSON/XML:
  https://github.com/gimler/symfony-rest-edition

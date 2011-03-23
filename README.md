RestBundle
==========

This Bundle provides various tools to rapidly develop RESTful API's with Symfony2

Installation
============

  1. Add this bundle to your project as Git submodules:

          $ git submodule add git://github.com/fos/RestBundle.git vendor/bundles/FOS/RestBundle

  2. Add the FOS namespace to your autoloader:

          // app/autoload.php
          $loader->registerNamespaces(array(
                'FOS' => __DIR__.'/../vendor/bundles',
                // your other namespaces
          ));

  3. Add this bundle to your application's kernel:

          // application/ApplicationKernel.php
          public function registerBundles()
          {
              return array(
                  // ...
                  new FOS\RestBundle\FOSRestBundle(),
                  // ...
              );
          }

Configuration
-------------

Registering a custom encoder requires modifying several configuration options.
Following an example adding support for a custom RSS encoder while removing
support for xml. Also the default Json encoder class is to modified:

# app/config.yml
fos_rest:
    fos_rest.formats:
            rss: my.encoder.rss
            xml: false
    class:
        json: MyProject\MyBundle\Serializer\Encoder\JsonEncoder

Note the service for the RSS encoder needs to be defined in a custom bundle:
        <service id="my.encoder.rss" class="MyProject\MyBundle\Serializer\Encoder\RSSEncoder" />

FrameworkBundle support
-----------------------

Make sure to disable rest annotations in the FrameworkBundle config, enable
or disable any of the other features depending on your needs:

    sensio_framework_extra:
        rest:    { annotations: false }
        router:  { annotations: true }

Finally enable the FrameworkBundle listener in the RestBundle:

    fos_rest:
        frameworkextra: true

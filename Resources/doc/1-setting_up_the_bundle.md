Step 1: Setting up the bundle
=============================

### A) Install FOSRestBundle

**Note:**

> This bundle recommends using [JMSSerializer](https://github.com/schmittjoh/serializer) which is 
> integrated into Symfony2 via [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle).
> If you want to use JMSSerializer, take a look into the instructions of the bundle to
> install it and set it up. You can also use [Symfony Serializer](https://github.com/symfony/Serializer).
> But in this case, you need to manually set it up and configure FOSRestBundle to use it
> via the ``service`` section in the app config

Simply run assuming you have installed composer.phar or composer binary:

``` bash
$ php composer.phar require friendsofsymfony/rest-bundle 1.3.*
```

### B) Enable the bundle

Finally, enable the bundle in the kernel:

``` php
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
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the index.](index.md)

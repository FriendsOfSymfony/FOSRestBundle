Step 1: Setting up the bundle
=============================

### A) Install FOSRestBundle

**Note:**

> This bundle recommends using [JMSSerializer](https://github.com/schmittjoh/serializer) which is 
> integrated into Symfony2 via [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle).
> Please follow the instructions of the bundle to add it to your composer.json and how to set it up.
> If you do not add a dependency to JMSSerializerBundle, you will need to manually setup an alternative
> service and configure the Bundle to use it via the ``service`` section in the app config

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
        
        // if you installed FOSRestBundle using composer you shouldn't forget
        // also registering JMSSerializerBundle.
        
        // new JMS\SerializerBundle\JMSSerializerBundle(),
    );
}
```

## That was it!

Check out the docs for information on how to use the bundle! [Return to the index.](index.md)

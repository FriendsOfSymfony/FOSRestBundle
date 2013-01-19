Step 1: Setting up the bundle
=============================
### A) Download FOSRestBundle

**Note:**

> This bundle recommends using [JMSSerializer](https://github.com/schmittjoh/serializer) which is 
> integrated into Symfony2 via [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle).
> Please follow the instructions of the bundle to add it to your composer.json and how to set it up.
> If you do not add a dependency to JMSSerializerBundle, you will need to manually setup an alternative
> service and configure the Bundle to use it via the ``service`` section in the app config

Ultimately, the FOSRestBundle files should be downloaded to the
`vendor/bundles/FOS/RestBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

```
[FOSRest]
    git=git://github.com/FriendsOfSymfony/FOSRest.git
    target=fos/FOS/Rest

[FOSRestBundle]
    git=git://github.com/FriendsOfSymfony/FOSRestBundle.git
    target=bundles/FOS/RestBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/FriendsOfSymfony/FOSRestBundle.git vendor/bundles/FOS/RestBundle
$ git submodule add git://github.com/FriendsOfSymfony/FOSRest.git vendor/fos/FOS/Rest
$ git submodule update --init
```

**Using composer**

Simply run assuming you have installed composer.phar or composer binary:

``` bash
$ composer require friendsofsymfony/rest-bundle
```

### B) Configure the Autoloader (not needed for composer)

Add the `FOS` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'FOS\\Rest' => __DIR__.'/../vendor/fos',
    'FOS'       => __DIR__.'/../vendor/bundles',
));
```

### C) Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\RestBundle\FOSRestBundle(),
        
        // if you installed FOSRestBundle using composer you shoudn't forget
        // also registering JMSSerializerBundle.
        
        // new JMS\SerializerBundle\JMSSerializerBundle(),
    );
}
```

## That was it!
Check out the docs for information on how to use the bundle! [Return to the index.](index.md)

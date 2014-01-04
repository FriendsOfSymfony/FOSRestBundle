# Sample FOSRest application with Chaplin.js and Backbone.js

[DunglasTodoMVCBundle](https://github.com/dunglas/DunglasTodoMVCBundle) is a Symfony implementation of the popular [TodoMVC](http://todomvc.com/) project.

This example app includes:
* A REST API built with [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle) using a [body listener](https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/3-listener-support.md#body-listener), a [format listener](https://github.com/FriendsOfSymfony/FOSRestBundle/blob/master/Resources/doc/3-listener-support.md#format-listener) and the `fos_rest.decoder.jsontoform` decoder
* JSON serialization of Doctrine entities through [JMSSerializerBundle](https://github.com/schmittjoh/JMSSerializerBundle)
* CSRF protection through [DunglasAngularCsrfBundle](https://github.com/dunglas/DunglasAngularCsrfBundle)
* A client built in [CoffeeScript](http://coffeescript.org/) with [Chaplin.js](http://chaplinjs.org/) and [Backbone.js](http://backbonejs.org/)

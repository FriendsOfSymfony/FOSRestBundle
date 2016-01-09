Status code when responding with no content
===========================================

In some use cases the api should not send any content, especially when deleting (*DELETE*) or updating (*PUT* or *PATCH*) a resource.

By default, ``FOSRestBundle`` will send a *204* status if the response is empty.
If you want to use another status code for empty responses, you can update your configuration file:

.. code-block:: yaml

    fost_rest:
        view:
            empty_content: 204

.. versionadded:: 2.0
  Until FOSRestBundle 2.0 this code will be used even if another code is configured manually inside the view object!

Changes in 2.0
--------------

In the ticket .. _#1278: https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1278 has been decided to make it possible for
users to adjust a custom status code. So if the configured code is *204*, but you want to send a 200 with empty content, you simply
have to adjust a status code to the *View* annotation or the view api of the *ControllerTrait*.

Another reason for this change was that for the *OPTIONS* http verb a *200* in favour of a *204* should be sent with empty
content as reported in .. _#1126: https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1126

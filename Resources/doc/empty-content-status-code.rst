Status code when responding with no content
===========================================

In some use cases the api should not send any content, especially when deleting (*DELETE*) or updating (*PUT* or *PATCH*) a resource.

By default, ``FOSRestBundle`` will send a *204* status if the response is empty.
If you want to use another status code for empty responses, you can update your configuration file:

.. code-block:: yaml

    fos_rest:
        view:
            empty_content: 204

.. versionadded:: 2.0
  Until FOSRestBundle 2.0 this code will be used even if another code is configured manually inside the view object!

If you don't want to use the default empty content status for a specific empty ``Response``, you just
have to set a status code manually thanks to the ``@View()`` annotation or the ``View`` class.

View Response listener
======================

The view response listener makes it possible to simply return a ``View``
instance from action controllers. The final output will then automatically be
processed via the listener by the ``fos_rest.view_handler`` service.

This requires adding the `SensioFrameworkExtraBundle`_ to your vendors.

Now inside a controller it's possible to simply return a ``View`` instance.

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\View\View;

    class UsersController
    {
        public function getUsersAction()
        {
            $view = View::create();

            // ...

            $view->setData($data);
            return $view;
        }
    }

As this feature is heavily based on the `SensioFrameworkExtraBundle`_, the
example can further be simplified by using the various annotations supported by
that bundle. There is also one additional annotation called ``@View()`` which
extends from the ``@Template()`` annotation.

Note: `SensioFrameworkExtraBundle`_ must be in your kernel if you want to use the annotations and ``sensio_framework_extra.view.annotations`` must be set to true.

The ``@View()`` and ``@Template()`` annotations behave essentially the same with
a minor difference. When ``view_response_listener`` is set to ``true`` instead
of ``force`` and ``@View()`` is not used, then rendering will be delegated to
`SensioFrameworkExtraBundle`_ (you must enable the view annotations in
`SensioFrameworkExtraBundle`_ for that case, use the default configuration).

.. code-block:: php

    <?php

    namespace AppBundle\Controller;

    use FOS\RestBundle\Controller\Annotations\View;

    class UsersController
    {
        /**
         * @View()
         */
        public function getUsersAction()
        {
            // ...

            return $data;
        }
    }

If you need to pass additional data to the template, not for serialization,
you should return a ``$view`` object with the data set by ``setTemplateData``.

.. code-block:: php

    <?php

    use FOS\RestBundle\View\View;

    /**
     * @View()
     */
    public function getUsersAction()
    {
        $view = View::create();

        // ...
        $view
            ->setData($data)
            ->setTemplateData($templateData)
        ;
        return $view;
    }

If ``@View()`` is used, the template variable name used to render templating
formats can be configured (default  ``'data'``):

.. code-block:: php

    <?php

    /**
     * @View(templateVar="users")
     */
    public function getUsersAction()
    {
        // ...
    }

The status code of the view can also be configured:

.. code-block:: php

    <?php

    /**
     * @View(statusCode=204)
     */
    public function deleteUserAction()
    {
        // ...
    }

The groups for the serializer can be configured as follows:

.. code-block:: php

    <?php

    /**
     * @View(serializerGroups={"group1", "group2"})
     */
    public function getUsersAction()
    {
        // ...
    }

Enabling the MaxDepth exclusion strategy support for the serializer can be
configured as follows:

.. code-block:: php

    <?php

    /**
     * @View(serializerEnableMaxDepthChecks=true)
     */
    public function getUsersAction()
    {
        // ...
    }

You can also define your serializer options dynamically:

.. code-block:: php

    <?php

    use FOS\RestBundle\Controller\Annotations\View as ViewAnnotation;
    use FOS\RestBundle\View\View;
    use FOS\RestBundle\Context\Context;

    /**
     * @ViewAnnotation()
     */
    public function getUsersAction()
    {
        $view = View::create();

        $context = new Context();
        $context->setVersion('1.0');
        $context->addGroup('user');

        $view->setSerializationContext($context);

        // ...
        $view
            ->setData($data)
            ->setTemplateData($templateData)
        ;
        return $view;
    }

See `this example code`_ for more details.

The ViewResponse listener will automatically populate your view with request
attributes if you do not provide any data when returning a view object. This
behaviour comes from `SensioFrameworkExtraBundle`_ and will automatically add
any variables listed in the ``_template_default_vars`` request attribute when no
data is supplied. In some cases, this is not desirable and can be disabled by
either supplying the data you want or disabling the automatic population of data
with the ``@View`` annotation:

.. code-block:: php

    /**
     * $user will no longer end up in the View's data.
     *
     * @View(populateDefaultVars=false)
     */
    public function getUserDetails(User $user)
    {
    }

.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html
.. _`this example code`: https://github.com/liip/LiipHelloBundle/blob/master/Controller/ExtraController.php

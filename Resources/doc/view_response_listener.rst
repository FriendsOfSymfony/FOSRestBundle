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

        $view->setContext($context);

        // ...
        $view
            ->setData($data)
        ;
        return $view;
    }

.. _`SensioFrameworkExtraBundle`: http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html

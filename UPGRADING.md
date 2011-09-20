Upgrading
=========

Note as FOSRestBundle is not yet declared stable, this document will only be updated
for major refactorings.

### upgrading from 0.5.0_old_serializer

 * The ViewInterface is gone so you might have to change your controller config if you refer to the fos_rest.view service.

 * The View class is now split into a View (simple data container) and a ViewHandler (contains the actual rendering logic).

    The following code would need to be changed:

        public function indexAction($name = null)
        {
            $view = $this->container->get('fos_rest.view');

            if (!$name) {
                $view->setResourceRoute('_welcome');
            } else {
                $view->setParameters(array('name' => $name));
                $view->setTemplate(new TemplateReference('LiipHelloBundle', 'Hello', 'index'));
            }

            return $view->handle();
        }

    To the following code:

        public function indexAction($name = null)
        {
            if (!$name) {
                $view = \FOS\RestBundle\View\RouteRedirectView::create('_welcome');
            } else {
                $view = \FOS\RestBundle\View\View::create(array('name' => $name))
                    ->setTemplate(new TemplateReference('LiipHelloBundle', 'Hello', 'index'));
                ;
            }

            return $this->container->get('fos_rest.view_handler')->handle($view);
        }

  * The custom Serializer class was removed instead JMSSerializerBundle is now used, which
    replaces the concept of normalizers/encoders with the concept of visitors and handler

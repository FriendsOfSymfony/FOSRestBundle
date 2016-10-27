Task CRUD tutorial
==================

Suppose that we have a fresh Symfony project with FOSRestBundle installed and configured in that way:

.. code-block:: yaml

    // app/config/config.yml
    fos_rest:
        view:
            view_response_listener: force # for always return View from FOSRestBundle Annotations
            formats:
                json: true
        body_listener: true # for decoding our request to forms
        routing_loader:
                default_format: json
                include_format: false # for remove ".json" suffix from all of our routes

A) Create Task entity
---------------------

For this tutorial we create ``Task`` entity with two simple fields ``name`` and ``completed``

.. code-block:: php

    // src/AppBundle/Entity/Task.php
    namespace AppBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Table(name="task")
     */
    class Task
    {
        /**
         * @ORM\Column(name="id", type="integer")
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(name="name", type="string", length=255)
         */
        private $name;

        /**
         * @ORM\Column(name="completed", type="boolean")
         */
        private $completed;

        public function getId()
        {
            return $this->id;
        }

        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }

        public function getName()
        {
            return $this->name;
        }

        public function setCompleted($completed)
        {
            $this->completed = $completed;

            return $this;
        }

        public function isCompleted()
        {
            return $this->completed;
        }
    }

B) Create TaskType form
-----------------------

For handling data coming from the request we need to create our ``TaskType.php`` file with standard content
(please note that we set up ``getName()`` method with ``'task'`` and ``'csrf_protection => false'`` - this is
important for creating requests which we will do later):

.. code-block:: php

    // src/AppBundle/Form/TaskType.php
    namespace AppBundle\Form;

    use Symfony\Component\Form\AbstractType;
    use Symfony\Component\Form\FormBuilderInterface;
    use Symfony\Component\OptionsResolver\OptionsResolver;

    class TaskType extends AbstractType
    {
        public function buildForm(FormBuilderInterface $builder, array $options)
        {
            $builder
                ->add('name')
                ->add('completed')
            ;
        }

        public function configureOptions(OptionsResolver $resolver)
        {
            $resolver->setDefaults(array(
                'data_class' => 'AppBundle\Entity\Task',
                'csrf_protection' => false,
            ));
        }
    }

C) Create TaskController
------------------------

For expose our REST API methods (routes) lets add the following controller:

.. code-block:: php

    // src/AppBundle/Controller/TaskController.php
    namespace AppBundle\Controller;

    use AppBundle\Entity\Task;
    use AppBundle\Form\TaskType;
    use FOS\RestBundle\Controller\Annotations\View;
    use FOS\RestBundle\Controller\FOSRestController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

    class TaskController extends FOSRestController
    {
        /**
         * @View
         */
        public function getTasksAction()
        {
            return $this->getRepository()->findAll();
        }

        /**
         * @View
         */
        public function getTaskAction(Request $request, $id)
        {
            $task = $this->getRepository()->find($id);

            if (!$task) {
                throw $this->createNotFoundException();
            }

            return $task;
        }

        /**
         * @View
         */
        public function postTasksAction(Request $request)
        {
            $task = new Task();
            $form = $this->createForm(TaskType::class, $task);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($task);
                $em->flush();

                return $task;
            }

            return $form;
        }

        /**
         * @View
         */
        public function putTasksAction(Request $request, $id)
        {
            $task = $this->getRepository()->find($id);
            if (!$task) {
                throw $this->createNotFoundException();
            }
            $form = $this->createForm(TaskType::class, $task, [
                'method' => 'PUT'
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($task);
                $em->flush();

                return $task;
            }

            return $form;
        }

        /**
         * @View
         */
        public function deleteTasksAction(Request $request, $id)
        {
            $em = $this->getDoctrine()->getManager();
            $task = $em->getRepository('AppBundle:Task')->find($id);
            if (!$task) {
                throw $this->createNotFoundException();
            }

            $em->remove($task);
            $em->flush();
        }

        private function getRepository()
        {
            return $this->getDoctrine()->getRepository('AppBundle:Task');
        }
    }

D) Update routing.yml
---------------------

Lte's update routing configuration:

.. code-block:: yml

    // app/config/routing.yml
    tasks:
        type:     rest
        resource: AppBundle\Controller\TaskController

E) Create database
------------------

We created our entity so we have to create database and schema:

.. code-block:: terminal

    $ bin/console doctrine:database:create
    $ bin/console doctrine:schema:create

F) Test our API!
----------------

After having set up our application it's time to test our REST API, so lets run the Symfony built-in server:

.. code-block:: terminal

    $ bin/console server:run

and test our endpoints with ``curl`` or I recommend Postman google-chrome extension:

.. code-block:: terminal

    # get list of tasks
    $ curl -X GET -H 'Content-Type: application/json' http://localhost:8000/tasks

    # create new task
    $ curl -X POST -H 'Content-Type: application/json' -d '{ "task": { "name": "name of the task", "completed": false } }' http://localhost:8000/tasks

    # show task
    $ curl -X GET -H 'Content-Type: application/json' http://localhost:8000/tasks/1

    # update existing task
    $ curl -X PUT -H 'Content-Type: application/json' -d '{ "task": { "name": "new name of the task", "completed": true } }' http://localhost:8000/tasks/1

    # delete task
    $ curl -X DELETE -H 'Content-Type: application/json' http://localhost:8000/tasks/1

That was it!

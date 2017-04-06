Upgrading From 1.x To 2.0
=========================

 * The `RedirectView` and `RouteRedirect` view classes were removed. Use
   `View::createRedirect()` and `View::createRouteRedirect()` instead.

   **Note**: the default status code for a route redirect has changed from
   HTTP_CREATED (201) to HTTP_FOUND (302).

 * The `FOS\RestBundle\Util\ViolationFormatter` class and the
   `FOS\RestBundle\Util\ViolationFormatterInterface` were removed.
   Catch specialized exception classes instead of checking specific
   exception messages.

 * The `ViolationFormatterInterface` argument of the constructor of
   the `ParamFetcher` class was removed.

 * The SensioFrameworkExtraBundle view annotations must be enabled to
   use the `ViewResponseListener`:

   ```yml
   # app/config/config.yml
   sensio_framework_extra:
       view:
           annotations: true
   ```

 * dropped support for the legacy
   `Symfony\Component\Validator\ValidatorInterface`

 * removed `FOS\RestBundle\Util\Codes` in favor of
   `Symfony\Component\HttpFoundation\Response` constants

 * compatibility with Symfony <2.7, JMS Serializer/SerializerBundle <1.0
   and SensioFrameworkExtraBundle <3.0 was dropped

 * constructor signature of DisableCSRFExtension was changed

 * constructor signatures of most of the classes which used the container
   were changed

 * removed `callback_filter` configuration option for the `jsonp_handler`

 * removed `fos_rest.format_listener.media_type` configuration option.
   Use the versioning section instead:

   ```yml
   # config.yml

   versioning: true
   ```

 * the `exception_wrapper_handler` config option was removed. Use normalizers instead.

   Before:
   ```yml
   # config.yml

   fos_rest:
       view:
           exception_wrapper_handler: AppBundle\ExceptionWrapperHandler
   ```
   ```php
   namespace AppBundle;

   class ExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
   {
       public function wrap($data)
       {
           return new ExceptionWrapper(array('status_code' => 'foo'));
       }
   }
   ```

   After (if you use the Symfony serializer):
   ```yml
   # services.yml

   services:
       app_bundle.exception_normalizer:
           class: AppBundle\Normalizer\ExceptionNormalizer
           tags:
               - { name: serializer.normalizer }
   ```
   ```php
   namespace AppBundle\Normalizer;

   use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

   class ExceptionNormalizer implements NormalizerInterface
   {
       public function normalize($object, $format = null, array $context = array())
       {
           return array('status_code' => 'foo');
       }

       public function supportsNormalization($data, $format = null)
       {
           return $data instanceof \My\Exception;
       }
   }
   ```

 * removed all `.class` parameters, instead overwriting services via
   explicit Bundle configuration is preferred

 * renamed `AbstractScalarParam::$array` to `AbstractScalarParam::$map`

   Before:
   ```php
   namespace AppBundle\Controller;

   class MyController
   {
       /**
        * @RequestParam(name="foo", array=true)
        */
       public function myAction()
       {
           // ...
       }
   }
   ```

   After:
   ```php
   namespace AppBundle\Controller;

   class MyController
   {
       /**
        * @RequestParam(name="foo", map=true)
        */
       public function myAction()
       {
           // ...
       }
   }
   ```   

 * added `ControllerTrait` for developers that prefer to use DI for their controllers instead of extending ``FOSRestController``

 * when having an action called ``lockUserAction``, then it will have to
   use the http method ``LOCK`` (RFC-2518) instead of ``PATCH``.
   The following methods are affected by this change:

   * `COPY`
   * `LOCK`
   * `MKCOL`
   * `MOVE`
   * `PROPFIND`
   * `PROPPATCH`
   * `UNLOCK`

 * removed the ability of the `AccessDeniedListener` to render a response.
   Use the FOSRestBundle or the twig exception controller in complement.

   Before:
   ```yml
   # config.yml

   fos_rest:
       access_denied_listener: true
   ```

   After:
   ```yml
   # config.yml

   fos_rest:
       access_denied_listener: true
       exception: true # Activates the FOSRestBundle exception controller
   ```

 * changed the priority of `RequestBodyParamConverter` to `-50`

 * made silent the `RequestBodyParamConverter` when a parameter is
   optional and it can't resolve it

 * removed the `format_negotiator` option `exception_fallback_format`;
   you can match the `ExceptionController` thanks to the `attributes`
   option instead

   Before:
   ```yml
   # config.yml

   fos_rest:
      format_listener:
          rules:
              - { path: ^/, fallback_format: html, exception_fallback_format: json }
   ```

   After:
   ```yml
   # config.yml

   fos_rest:
      format_listener:
          rules:
              - { path: ^/, fallback_format: json, attributes: { _controller: FOS\RestBundle\Controller\ExceptionController } }
              - { path: ^/, fallback_format: html }
   ```

 * `View::setSerializationContext` and `View::getSerializationContext`
   have been removed. Use `View::setContext` and `View::getContext`
   together with the new Context class instead.

   Before:
   ```php
   use JMS\Serializer\SerializationContext;

   $view = new View();

   $context = new SerializationContext();
   $view->setSerializationContext($context);

   $context = $view->getSerializationContext();
   ```

   After:
   ```php
   use FOS\RestBundle\Context\Context;

   $view = new View();

   $context = new Context();
   $view->setContext($context);

   $context = $view->getContext();
   ```

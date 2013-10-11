Step 4: ExceptionController support
===================================

When implementing an API it is also necessary to handle exceptions in a RESTful
way, while ensuring that no security sensitive information leaks out. This bundle
provides an extra controller for that job. Using this custom ExceptionController
it is possible to leverage the View layer when building responses for uncaught
Exceptions.

To enable the RestBundle view-layer-aware ExceptionController update the twig
section of your config as follows:

```yaml
# app/config/config.yml
twig:
    exception_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'
```

To map Exception classes to HTTP response status codes an “exception map” may
be configured, where the keys match a fully qualified class name and the values
are either an integer HTTP response status code or a string matching a class
constant of the ``FOS\RestBundle\Util\Codes`` class:

```yaml
# app/config/config.yml
fos_rest:
    exception:
        codes:
            'Symfony\Component\Routing\Exception\ResourceNotFoundException': 404
            'Doctrine\ORM\OptimisticLockException': HTTP_CONFLICT
        messages:
            'Acme\HelloBundle\Exception\MyExceptionWithASafeMessage': true
```

If you want to display the message from the exception in the content of the
response, add the exception to the messages map as well. If not only the status
code will be returned.

If you know what status code you want to return you do not have to add a
mapping, you can do this in your controller:

```php
<?php
class UsersController extends Controller
{
    public function postUserCommentsAction($slug)
    {
        if (!$this->validate($slug)) {
            throw new HttpException(400, "New comment is not valid.");
        }
    }
}
```

See the following example configuration for more details:
https://github.com/liip-forks/symfony-standard/blob/techtalk/app/config/config.yml

## That was it!
[Return to the index](index.md) or continue reading about [Automatic route generation: single RESTful controller](5-automatic-route-generation_single-restful-controller.md).

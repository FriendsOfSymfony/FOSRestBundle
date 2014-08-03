Full default annotations
==========================

### Param fetcher

#### QueryParam

```php
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * @QueryParam(
 *   name="",
 *   key=null,
 *   requirements="",
 *   default=null,
 *   description="",
 *   strict=false,
 *   array=false,
 *   nullable=false
 * )
 */
```

#### RequestParam

```php
use FOS\RestBundle\Controller\Annotations\RequestParam;

/**
 * @RequestParam(
 *   name="",
 *   key=null,
 *   requirements="",
 *   default=null,
 *   description="",
 *   strict=true,
 *   array=false,
 *   nullable=false
 * )
 */
```

### View

```php
use FOS\RestBundle\Controller\Annotations\View;

/**
 * @View(
 *  templateVar="",
 *  statusCode=null,
 *  serializerGroups={},
 *  populateDefaultVars=true,
 *  serializerEnableMaxDepthChecks=false
 * )
 */
```

### Routing

#### Route prefix

```php
use FOS\RestBundle\Controller\Annotations\Prefix;

/**
 * @Prefix("")
 */
```

#### Route name prefix

```php
use FOS\RestBundle\Controller\Annotations\NamePrefix;

/**
 * @NamePrefix("")
 */
```

#### Route

RestBundle extends the [@Route](http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/routing.html) annotation from Symfony.

@Delete @Get @Head @Link @Patch @Post @Put @Unlink have the same options as @Route

When using `symfony/routing:>=2.4` (or the full framework) you have access to the expression language component and can
add conditions to your routing configuration with annotations (see: [Routing Conditions](http://symfony.com/doc/current/book/routing.html#book-routing-conditions)).

example syntax:

```php
use FOS\RestBundle\Controller\Annotations\Route
/**
* @Route("", condition="context.getMethod() in ['GET', 'HEAD'] and request.headers.get('User-Agent') matches '/firefox/i'")
*/
```


[Return to the index](index.md).

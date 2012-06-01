Step 7: Url Serializer Support
=============================


The Url serializer support makes it possible to serialize url into your object with Symfony's routing capability.

```yaml
# app/config/config.yml
jms_serializer:
    handlers:
        routing: true 
````
This will tell JMSSerializerBundle to activate RoutingSerializer.

```yaml
# app/routing/routing.yml
api_get_categories:
    pattern: /api/categories
    defaults:
        _controller: AcmeDemoBundle:Category:getCategories

api_get_category_books:
    pattern: /api/categories/{categoryId}/books
    defaults:
        _controller: AcmeDemoBundle:Category:getCategoryBooks
````


```php
<?php

namespace Acme\DemoBundle\Entity;

use FOS\RestBundle\Serializer\Annotations\Url;
use FOS\RestBundle\Serializer\Annotations\Param;

/**
 * @Url(field="bookUrl",
 *      routeName="api_get_category_books",
 *      params={
 *          @Param(key="categoryId", field="id")
 *      }
 *  )
 */
class Category
{
    private $id;
    private $title;
    private $bookUrl;

    //getter and setter

    public function setBookUrl($url)
    {
        $this->bookUrl = $url;
    }

    public function getBookUrl()
    {
        return $this->bookUrl;
    }
}
```

```php
<?php

namespace Acme\DemoBundle\Controller;

class CategoryController extends Controller
{
    public function getCategoriesAction()
    {
        //get categories
        //create view

        return $this->get('fos_rest.view_handler')->handle($view);
    }

    public function getCategoryBooksAction($categoryId)
    {
        ...
    }
}
```

Now when I go to /api/categories.json it will returns a list of categories.

```json
[
    {
        "id": 1,
        "title": "some-title",
        "bookUrl": "/api/categories/1/books"
    }
]
```

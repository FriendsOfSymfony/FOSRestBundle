Manual definition of routes
=====================================

If the automatic route generation does not fit your needs, you can manually define a route using simple annotations. This is very helpful if you want to have more than 1 url parameter without having a static word in between them.

For a full list of annotations check out FOS/RestBundle/Controller/Annotations

## Delete Route Definition
	use FOS\RestBundle\Controller\Annotations\Delete;

	/**
	 * DELETE Route annotation.
	 * @Delete("/likes/{type}/{typeId}")
	 */

## Head Route Definition
	use FOS\RestBundle\Controller\Annotations\Head;

	/**
	 * HEAD Route annotation.
	 * @Head("/likes/{type}/{typeId}")
	 */

## Get Route Definition
	use FOS\RestBundle\Controller\Annotations\Get;

	/**
	 * GET Route annotation.
	 * @Get("/likes/{type}/{typeId}")
	 */
	 
## Patch Route Definition
	use FOS\RestBundle\Controller\Annotations\Patch;

	/**
	 * PATCH Route annotation.
	 * @Patch("/likes/{type}/{typeId}")
	 */
	 
## Post Route Definition
	use FOS\RestBundle\Controller\Annotations\Post;

	/**
	 * POST Route annotation.
	 * @Post("/likes/{type}/{typeId}")
	 */
	 
## Put Route Definition
	use FOS\RestBundle\Controller\Annotations\Put;

	/**
	 * PUT Route annotation.
	 * @Put("/likes/{type}/{typeId}")
	 */

[Return to the index](index.md) or continue with the [Full default configuration](configuration-reference.md).

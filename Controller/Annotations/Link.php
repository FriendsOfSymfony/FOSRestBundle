<?php
namespace FOS\RestBundle\Controller\Annotations;

/**
 * LINK Route annotation class.
 * @Annotation
 */
class Link extends Route
{
    public function getMethod()
    {
        return 'LINK';
    }
}

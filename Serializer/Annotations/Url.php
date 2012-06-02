<?php

namespace FOS\RestBundle\Serializer\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class Url
{
    public $routeName;
    public $params = array();
}

<?php

namespace FOS\RestBundle\Serializer\Annotations;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
final class Url
{
    public $field;
    public $routeName;
    public $params = array();
}

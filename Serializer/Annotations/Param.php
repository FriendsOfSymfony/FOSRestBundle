<?php

namespace FOS\RestBundle\Serializer\Annotations;

/**
 * @Annotation
 * @Target({"ALL"})
 */
final class Param 
{
    public $key;
    public $field;
}

<?php

namespace FOS\RestBundle\View;


use FOS\RestBundle\Util\ExceptionWrapper;

/**
 * @author: Toni Van de Voorde (toni [dot] vdv [AT] gmail [dot] com)
 */
class ExceptionWrapperHandler implements ExceptionWrapperHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function wrap($data)
    {
        return new ExceptionWrapper($data);
    }
}
<?php

namespace FOS\RestBundle\View;

/**
 * @author Toni Van de Voorde (toni [dot] vdv [AT] gmail [dot] com)
 */
interface ExceptionWrapperHandlerInterface
{
    /**
     * @param array $data
     *
     * @return mixed
     */
    public function wrap($data);
}
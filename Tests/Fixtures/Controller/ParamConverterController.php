<?php

namespace FOS\RestBundle\Tests\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @author Toni Van de Voorde <toni.vdv@gmail.com>
 */
class ParamConverterController
{
    /**
     * @ParamConverter("something", converter="fos_rest.request_body")
     *
     * @param Something $something
     */
    public function postSomethingAction(Something $something)
    {
    }
}

final class Something
{
    public $id;
}

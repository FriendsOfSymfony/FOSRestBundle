<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Request;

use FOS\RestBundle\Request\RequestBodyParamConverter;

/**
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverterTest extends AbstractRequestBodyParamConverterTest
{
    protected $serializer;
    protected $converter;

    public function setUp()
    {
        // skip the test if the installed version of SensioFrameworkExtraBundle
        // is not compatible with the RequestBodyParamConverter class
        $parameter = new \ReflectionParameter(
            [
                'Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface',
                'supports',
            ],
            'configuration'
        );
        if ('Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter' != $parameter->getClass()->getName()) {
            $this->markTestSkipped(
                'skipping RequestBodyParamConverterTest due to an incompatible version of the SensioFrameworkExtraBundle'
            );
        }

        parent::setUp();
    }

    protected function getConverterBuilder()
    {
        return $this->getMockBuilder('FOS\RestBundle\Request\RequestBodyParamConverter');
    }
}

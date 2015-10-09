<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;

/**
 * This code is needed for SensioFrameworkExtraBundle 2.x compatibility
 * https://github.com/FriendsOfSymfony/FOSRestBundle/issues/622.
 *
 * @author Tyler Stroud <tyler@tylerstroud.com>
 */
class RequestBodyParamConverter20 extends AbstractRequestBodyParamConverter
{
    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        return $this->execute($request, $configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        return null !== $configuration->getClass();
    }
}

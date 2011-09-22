<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DecoderProvider;

/**
 * Provides encoders through the Symfony2 DIC
 *
 * @author Igor Wiedler <igor@wiedler.ch>
 */
class ContainerDecoderProvider extends ContainerAware implements DecoderProviderInterface
{
    public function getDecoder($id)
    {
        return $this->container->get($id);
    }
}

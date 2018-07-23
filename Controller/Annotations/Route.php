<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller\Annotations;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route as LegacyBaseRoute;
use Symfony\Component\Routing\Annotation\Route as Symfony4BaseRoute;

if (class_exists(LegacyBaseRoute::class)) {
    class BaseRoute extends LegacyBaseRoute {}
} else {
    class BaseRoute extends Symfony4BaseRoute {}
}

/**
 * Route annotation class.
 *
 * @Annotation
 */
class Route extends BaseRoute
{
    public function __construct(array $data)
    {
        parent::__construct($data);

        if (!$this->getMethods()) {
            $this->setMethods((array) $this->getMethod());
        }
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return;
    }
}

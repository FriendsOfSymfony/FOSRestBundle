<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
    new \Symfony\Bundle\TwigBundle\TwigBundle(),
    new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
    new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
    new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
    new \FOS\RestBundle\FOSRestBundle(),
    new \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\TestBundle(),
];

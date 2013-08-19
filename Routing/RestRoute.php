<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing;

use Symfony\Component\Routing\Route;

/**
 * Restful route.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RestRoute extends Route
{
    private $placeholders;
    private $relName;

    /**
     * Set argument names of the route.
     *
     * @param array $placeholders argument names
     */
    public function setPlaceholders($placeholders)
    {
        $this->placeholders = $placeholders;
    }

    /**
     * Returns argument names of the route.
     *
     * @return array
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * Returns rel name of the route.
     *
     * @return string
     */
    public function getRelName()
    {
        return $this->relName;
    }

    /**
     * Set rel name of the route.
     *
     * @param string $relName rel name
     */
    public function setRelName($relName)
    {
        $this->relName = $relName;
    }

    public function serialize()
    {
        $data = parent::serialize();
        $data = unserialize($data);
        $data['placeholders'] = $this->placeholders;
        $data['relName'] = $this->relName;
        return serialize($data);
    }

    public function unserialize($data)
    {
        parent::unserialize($data);
        $data = unserialize($data);
        $this->placeholders = $data['placeholders'];
        $this->relName = $data['relName'];
    }
}

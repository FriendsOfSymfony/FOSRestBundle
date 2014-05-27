<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

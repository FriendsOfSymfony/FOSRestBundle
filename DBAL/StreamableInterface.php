<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DBAL;

use FOS\RestBundle\Serializer\Serializer;
use FOS\RestBundle\Context\Context;

interface StreamableInterface
{
    
    /**
     * @param Serializer $serializer
     * @param string $format
     * @param Context $context
     */
    public function writeHeader(Serializer $serializer, $format, Context $context);
    
    /**
     * @param Serializer $serializer
     * @param array $data
     * @param string $format
     * @param Context $context
     * @param int $iteration
     * @param int $totalIterations
     */
    public function writeNode(Serializer $serializer, array $data, $format, Context $context, $iteration, $totalIterations);
    
    /**
     * @param Serializer $serializer
     * @param string $format
     * @param Context $conext
     */
    public function writeFooter(Serializer $serializer, $format, Context $conext);
    
    /**
     * @return array
     */
    public function fetch();
    
    /**
     * @return int
     */
    public function rowCount();
    
    /**
     * @return bool
     */
    public function fetchAllInTemplates();
}

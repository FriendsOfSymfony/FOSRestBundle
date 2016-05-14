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

/**
 * Interface that allows automatic pagination of results.
 *
 * @author JD Williams <jd@rottylabs.com>
 */
interface PageableInterface
{
    
    /**
     * @return int
     */
    public function rowCount();
    
    /**
     * @param int $offset
     * @param int $limit
     * 
     * @return array
     */
    public function fetchPage($offset, $limit);
    
    /**
     * @param int $offset
     * @param int $limit
     * 
     * @return string
     */
    public function getRange($offset, $limit);
    
    /**
     * @return string 
     */
    public function getRangeHeader();
    
    /**
     * @return string 
     */
    public function getOffsetParam();
    
    /**
     * @return string
     */
    public function getLimitParam();
}
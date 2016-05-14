<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DBAL\Doctrine;

use FOS\RestBundle\DBAL\StreamableInterface;
use FOS\RestBundle\DBAL\PageableInterface;
use FOS\RestBundle\Serializer\Serializer;
use FOS\RestBundle\Context\Context;

/**
 * Dao for returning streamable/pageable results from a Doctrine QueryBuilder.
 *
 * @author JD Williams <jd@rottylabs.com>
 */
class Dao implements PageableInterface, StreamableInterface
{

    /**
     * @var string
     */
    protected $rangeHeader;
    
    /**
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    protected $queryBuilder;
    
    /**
     * @var int
     */
    protected $rowCount;
    
    /**
     * @var \Doctrine\DBAL\Statement
     */
    protected $statement;
    
    /**
     * @var string
     */
    protected $offsetParam;
    
    /**
     * @var string
     */
    protected $limitParam;
    
    /**
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param string $contentRange
     */
    public function __construct(
        \Doctrine\DBAL\Query\QueryBuilder $queryBuilder, 
        $contentRange = "Content-Range",
        $offsetParam = "offset",
        $limitParam = "limit"
    )
    {
        $this->queryBuilder = $queryBuilder;
        $this->rangeHeader = $contentRange;
        $this->offsetParam = $offsetParam;
        $this->limitParam = $limitParam;
    }

    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\PageableInterface::getRange()
     */
    public function getRange($offset, $limit)
    { 
        return sprintf(
            "%d-%d/%d",
            $offset,
            $offset + $limit,
            $this->rowCount()
        );
    }

    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\PageableInterface::fetchPage()
     */
    public function fetchPage($offset, $limit)
    {
        $this->queryBuilder->setFirstResult($offset);
        $this->queryBuilder->setMaxResults($limit);
        return $this->queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\PageableInterface::getOffsetParam()
     */
    public function getOffsetParam()
    {
        return $this->offsetParam;
    }
    
    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\PageableInterface::getLimitParam()
     */
    public function getLimitParam()
    {
        return $this->limitParam;
    }

    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\PageableInterface::rowCount()
     */
    public function rowCount()
    { 
        if (null !== $this->rowCount) {
            return $this->rowCount;
        }
        
        $this->queryBuilder->setFirstResult(null);
        $this->queryBuilder->setMaxResults(null);
        
        $outerQuery = $this->queryBuilder->getConnection()->createQueryBuilder();
        $outerQuery->select('count(*)')
            ->from(sprintf("(%s)", $this->queryBuilder->getSQL()), 'q');
        foreach ($this->queryBuilder->getParameters() as $key => $value) {
            $outerQuery->setParameter($key, $value);
        }
        $statement = $outerQuery->execute();
        $result = $statement->fetch();
        
        $this->rowCount = current($result);
        return $this->rowCount;
    }

    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\PageableInterface::getRangeHeader()
     */
    public function getRangeHeader()
    { 
        return $this->rangeHeader;
    }
    
    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\StreamableInterface::fetch()
     */
    public function fetch()
    {
        if (null === $this->statement) {
            $this->statement = $this->queryBuilder->execute();
        }
        
        return $this->statement->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\StreamableInterface::writeHeader()
     */
    public function writeHeader(Serializer $serializer, $format, Context $context)
    {
        if ($format === 'json') {
            echo '[';
        } else if ($format === 'xml') {
            echo '<?xml version="1.0" encoding="UTF-8"?><result>';
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\StreamableInterface::writeNode()
     */
    public function writeNode(Serializer $serializer, array $data, $format, Context $context, $iteration, $totalIterations)
    {
        if ($format === 'xml') {
            $domDocument = new \DOMDocument();
            $element = $domDocument->createElement("row");
            foreach ($data as $key => $value) {
                $column = $domDocument->createElement("column");
                $column->setAttribute("name", $key);
                $column->nodeValue = $value;
                $element->appendChild($column);
            }
            echo $domDocument->saveXML($element);
        } else {
            echo $serializer->serialize($data, $format, $context);
        }
        
        if ($format === 'json' && $iteration < $totalIterations) {
            echo ',';
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \FOS\RestBundle\DBAL\StreamableInterface::writeFooter()
     */
    public function writeFooter(Serializer $serializer, $format, Context $conext)
    {
        if ($format === 'json') {
            echo ']';
        } else if ($format === 'xml') {
            echo '</result>';
        }
    }
}
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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * View annotation class.
 *
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class View extends Template
{
    /**
     * @var int|null
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $serializerGroups;

    /**
     * @var bool
     */
    protected $serializerEnableMaxDepthChecks;

    /**
     * @param array|string $data
     */
    public function __construct(
        $data = [],
        array $vars = [],
        bool $isStreamable = false,
        array $owner = [],
        ?int $statusCode = null,
        array $serializerGroups = [],
        bool $serializerEnableMaxDepthChecks = false
    ) {
        parent::__construct($data, $vars, $isStreamable, $owner);

        $values = is_array($data) ? $data : [];
        $this->statusCode = $values['statusCode'] ?? $statusCode;
        $this->serializerGroups = $values['serializerGroups'] ?? $serializerGroups;
        $this->serializerEnableMaxDepthChecks = $values['serializerEnableMaxDepthChecks'] ?? $serializerEnableMaxDepthChecks;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param array $serializerGroups
     */
    public function setSerializerGroups($serializerGroups)
    {
        $this->serializerGroups = $serializerGroups;
    }

    /**
     * @return array
     */
    public function getSerializerGroups()
    {
        return $this->serializerGroups;
    }

    /**
     * @param bool $serializerEnableMaxDepthChecks
     */
    public function setSerializerEnableMaxDepthChecks($serializerEnableMaxDepthChecks)
    {
        $this->serializerEnableMaxDepthChecks = $serializerEnableMaxDepthChecks;
    }

    /**
     * @return bool
     */
    public function getSerializerEnableMaxDepthChecks()
    {
        return $this->serializerEnableMaxDepthChecks;
    }
}

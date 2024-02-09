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

if (class_exists(Template::class)) {
    /**
     * Compat class for applications where SensioFrameworkExtraBundle is installed, to be removed when compatibility with the bundle is no longer provided.
     *
     * @internal
     */
    abstract class CompatView extends Template
    {
    }
} else {
    /**
     * Compat class for applications where SensioFrameworkExtraBundle is not installed.
     *
     * @internal
     */
    abstract class CompatView
    {
        /**
         * The controller (+action) this annotation is set to.
         *
         * @var array
         *
         * @note This property is declared within this compat class to not conflict with the {@see Template::$owner}
         *       property when SensioFrameworkExtraBundle is present.
         */
        protected $owner = [];

        /**
         * @note This method is declared within this compat class to not conflict with the {@see Template::setOwner}
         *       method when SensioFrameworkExtraBundle is present.
         */
        public function setOwner(array $owner)
        {
            $this->owner = $owner;
        }

        /**
         * The controller (+action) this annotation is attached to.
         *
         * @return array
         *
         * @note This method is declared within this compat class to not conflict with the {@see Template::getOwner}
         *        method when SensioFrameworkExtraBundle is present.
         */
        public function getOwner()
        {
            return $this->owner;
        }
    }
}

/**
 * View annotation class.
 *
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class View extends CompatView
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
        if ($this instanceof Template) {
            trigger_deprecation('friendsofsymfony/rest-bundle', '3.7', 'Extending from "%s" in "%s" is deprecated, the $vars and $isStreamable constructor arguments will not be supported when "sensio/framework-extra-bundle" is not installed and will be removed completely in 4.0.', Template::class, static::class);

            parent::__construct($data, $vars, $isStreamable, $owner);
        } elseif ([] !== $vars) {
            trigger_deprecation('friendsofsymfony/rest-bundle', '3.7', 'Extending from "%s" in "%s" is deprecated and "sensio/framework-extra-bundle" is not installed, the $vars and $isStreamable constructor arguments will be ignored and removed completely in 4.0.', Template::class, static::class);
        }

        $values = is_array($data) ? $data : [];
        $this->statusCode = $values['statusCode'] ?? $statusCode;
        $this->serializerGroups = $values['serializerGroups'] ?? $serializerGroups;
        $this->serializerEnableMaxDepthChecks = $values['serializerEnableMaxDepthChecks'] ?? $serializerEnableMaxDepthChecks;

        // Use the setter to initialize the owner; when extending the Template class, the property will be private
        $this->setOwner($values['owner'] ?? $owner);
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

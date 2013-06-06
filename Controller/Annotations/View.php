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
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
class View extends Template
{
    /**
     * @var string
     */
    protected $templateVar;

    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var array
     */
    protected $serializerGroups;

    /**
     * Returns the annotation alias name.
     *
     * @return string
     * @see Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface
     */
    public function getAliasName()
    {
        return 'view';
    }

    /**
     * Sets the template var name to be used for templating formats.
     *
     * @param string $templateVar
     */
    public function setTemplateVar($templateVar)
    {
        $this->templateVar = $templateVar;
    }

    /**
     * Returns the template var name to be used for templating formats.
     *
     * @return string
     */
    public function getTemplateVar()
    {
        return $this->templateVar;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @var array $serializerGroups
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
}

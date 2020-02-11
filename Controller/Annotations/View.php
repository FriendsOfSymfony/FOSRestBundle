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
     * @var bool
     */
    protected $populateDefaultVars = true;

    /**
     * @var bool
     */
    protected $serializerEnableMaxDepthChecks;

    /**
     * Sets the template var name to be used for templating formats.
     *
     * @deprecated since 2.8
     *
     * @param string $templateVar
     */
    public function setTemplateVar($templateVar)
    {
        if (1 === func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        $this->templateVar = $templateVar;
    }

    /**
     * Returns the template var name to be used for templating formats.
     *
     * @deprecated since 2.8
     *
     * @return string
     */
    public function getTemplateVar()
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

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
     * @deprecated since 2.8
     *
     * @param bool $populateDefaultVars
     */
    public function setPopulateDefaultVars($populateDefaultVars)
    {
        if (1 === func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        $this->populateDefaultVars = (bool) $populateDefaultVars;
    }

    /**
     * @deprecated since 2.8
     *
     * @return bool
     */
    public function isPopulateDefaultVars()
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        return $this->populateDefaultVars;
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

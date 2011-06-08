<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface,
    Symfony\Bundle\FrameworkBundle\Templating\EngineInterface,
    Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Defines the interface of templating aware encoders
 *
 * @author Lukas Smith <smith@pooteeweet.org>
 */
interface TemplatingAwareEncoderInterface extends EncoderInterface
{
    /**
     * Sets the Templating object
     *
     * @param EngineInterface $templating
     */
    function setTemplating(EngineInterface $templating);

    /**
     * Gets the Templating object
     *
     * @return EngineInterface template engine
     */
    function getTemplating();

    /**
     * Sets the template
     *
     * @param string|TemplateReferenceInterface $template template to be used in the encoding
     */
    function setTemplate($template);

    /**
     * Gets the template
     *
     * @return string|TemplateReferenceInterface template to be used in the encoding
     */
    function getTemplate();
}

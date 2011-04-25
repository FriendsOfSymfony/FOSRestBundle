<?php

namespace FOS\RestBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface,
    Symfony\Bundle\FrameworkBundle\Templating\EngineInterface,
    Symfony\Component\Templating\TemplateReferenceInterface;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

<?php

namespace FOS\RestBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Templating\EngineInterface;

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
     * @return SerializerInterface
     */
    function getTemplating();

    /**
     * Sets the template
     *
     * @param string $template
     */
    function setTemplate($template);

    /**
     * Gets the template
     *
     * @return string
     */
    function getTemplate();
}

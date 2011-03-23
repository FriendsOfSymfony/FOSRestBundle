<?php

namespace FOS\RestBundle\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Templating\EngineInterface;

/**
 * Encodes HTML data
 *
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class HtmlEncoder extends XmlEncoder implements TemplatingAwareEncoderInterface
{
    protected $templating;
    protected $template;

    /**
     * {@inheritdoc}
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplating()
    {
        return $this->templating;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format)
    {
        if (null === $this->template) {
            throw new \UnexpectedValueException('A template must be provided to encode to HTML');
        }

        return $this->templating->render($this->template, (array)$data);
    }
}

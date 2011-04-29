<?php

namespace FOS\RestBundle\Serializer\Encoder;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface,
    Symfony\Component\Serializer\Encoder\AbstractEncoder,
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
 * Encodes HTML data
 *
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class HtmlEncoder extends AbstractEncoder implements TemplatingAwareEncoderInterface
{
    /**
     * @var EngineInterface template engine instance
     */
    protected $templating;

    /**
     * @var string|TemplateReferenceInterface template
     */
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
     * 
     * @throws \InvalidArgumentException if the template neither is a string nor implement TemplateReferenceInterface
     */
    public function setTemplate($template)
    {
        if (!(is_string($template) || $template instanceof TemplateReferenceInterface)) {
            throw new \InvalidArgumentException('The template should be a string or implement TemplateReferenceInterface');
        }
        
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
        $template = $this->getTemplate();
        if (null === $template) {
            throw new \UnexpectedValueException('A template must be provided to encode to HTML');
        }

        return $this->templating->render($template, (array)$data);
    }
}

<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Encoder;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface,
    Symfony\Component\Serializer\Encoder\NormalizationAwareInterface,
    Symfony\Component\Serializer\Encoder\SerializerAwareEncoder,
    Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Encodes HTML data
 *
 * @author Lukas Smith <smith@pooteeweet.org>
 */
class HtmlEncoder extends SerializerAwareEncoder implements NormalizationAwareInterface, TemplatingAwareEncoderInterface
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

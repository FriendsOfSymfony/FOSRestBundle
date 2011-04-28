<?php

namespace FOS\RestBundle\Tests\Serialize\Encoder;

use FOS\RestBundle\Serializer\Encoder\HtmlEncoder,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

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
 * Html encoder test
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class HtmlEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testSetTemplateTemplateFormat()
    {
        $encoder = new HtmlEncoder();
        
        $encoder->setTemplate('foo');
        $this->assertEquals('foo', $encoder->getTemplate());
        
        $encoder->setTemplate($template = new TemplateReference());
        $this->assertEquals($template, $encoder->getTemplate());
        
        try {
            $encoder->setTemplate(array());
            $this->fail('->setTemplate() should accept strings and TemplateReferenceInterface instances only');
        } catch (\InvalidArgumentException $e) {            
        }
    }
    
}
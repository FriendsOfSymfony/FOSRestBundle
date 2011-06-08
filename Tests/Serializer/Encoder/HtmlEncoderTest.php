<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Serialize\Encoder;

use FOS\RestBundle\Serializer\Encoder\HtmlEncoder,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

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

<?php

namespace FOS\RestBundle\Tests\View;

use FOS\RestBundle\View\View,
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
 * View test
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testSetTemplateTemplateFormat()
    {
        $view = new View();
        
        $view->setTemplate('foo');
        $this->assertEquals('foo', $view->getTemplate());
        
        $view->setTemplate($template = new TemplateReference());
        $this->assertEquals($template, $view->getTemplate());
        
        try {
            $view->setTemplate(array());
            $this->fail('->setTemplate() should accept strings and TemplateReference instances only');
        } catch (\InvalidArgumentException $e) {            
        }
    }
    
}
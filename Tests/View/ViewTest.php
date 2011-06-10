<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\View;

use FOS\RestBundle\View\View,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

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

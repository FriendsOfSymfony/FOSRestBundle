<?php
/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\SerializerBundle\Serializer\SerializerInterface;
use JMS\SerializerBundle\Exception\RuntimeException;

/**
 * RequestContentParamConverter
 *
 * It supports deserializing content of the request into needed object.
 * 
 * Based on http://php-and-symfony.matthiasnoback.nl/2012/03/symfony2-creating-a-paramconverter-for-deserializing-request-content/
 * by Matthias Noback (http://php-and-symfony.matthiasnoback.nl)
 * 
 * @author Antoni Orfin <a.orfin@imagin.com.pl>
 * @copyright (c) 2012  IMAGIN IT Marcin Engelmann
 */
class RequestContentParamConverter implements ParamConverterInterface {
    
    /**
     * Serializer instance.
     * 
     * @var SerializerInterface
     */
    private $serializer;
    
    /**
     * Optional options.
     * 
     * @var array
     */
    private $options;
    
    public function __construct(SerializerInterface $serializer, array $options = array()) {
        $this->serializer = $serializer;
        $this->options = $options;
    }
    
    /**
     * Only objects are supported.
     * 
     * @param ConfigurationInterface $configuration
     * @return boolean 
     */
    public function supports(ConfigurationInterface $configuration) {
        if (!$configuration->getClass()) {
            return false;
        }

        return true;
    }

    /**
     * Deserialize request content to object.
     * 
     * @param Request $request
     * @param ConfigurationInterface $configuration
     * @throws RuntimeException Only if request_content_param_converter.exception_on_fault is set to true
     */
    public function apply(Request $request, ConfigurationInterface $configuration) {
        $class = $configuration->getClass();
        
        $contentType = $request->headers->get('Content-Type');

        $format = null === $contentType
            ? $request->getRequestFormat()
            : $request->getFormat($request->headers->get('Content-Type'));

        try {
            $object = $this->serializer->deserialize(
                    $request->getContent(), $class, $format
            );
        }
        catch (RuntimeException $e) {
            
            /**
             * If request_content_param_converter.exception_on_fault is set to false
             * don't throw Exception but new Object (using empty constructor)
             */
            
            if(isset($this->options['exception_on_fault']) && $this->options['exception_on_fault'] === true) {
                throw $e;
            }
            else {
                $object = new $class;
            }
        }

        // set the object as the request attribute with the given name
        // (this will later be an argument for the action)
        $request->attributes->set($configuration->getName(), $object);
    }

}

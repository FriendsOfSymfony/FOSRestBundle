<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Form\FormInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Serializer\Encoder\TemplatingAwareEncoderInterface;

/**
 * ViewInterfce may be used in controllers to build up a response in a format agnostic way
 * The ViewInterface implementation takes care of encoding your data in json, xml, or renders a
 * template for html via the Serializer component.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
interface ViewInterface
{
    /**
     * Resets the state of this view instance
     */
    function reset();

    /**
     * Verifies whether the given format is supported by this view
     *
     * @param string $format format name
     *
     * @return Boolean
     */
    function supports($format);

    /**
     * Registers a custom handler
     *
     * The handler must have the following signature: handler($viewObject, $request, $response)
     * It can use the methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     *
     * @param string $format the format that is handled
     * @param callback $callback handler callback
     */
    function registerHandler($format, $callback);

    /**
     * Sets a redirect using a route and parameters
     *
     * @param string $route route name
     * @param array $parameters route parameters
     * @param int $code HTTP status code
     */
    function setResourceRoute($route, array $parameters = array(), $code = Codes::HTTP_CREATED);

    /**
     * Sets a redirect using a route and parameters
     *
     * @param string $route route name
     * @param array $parameters route parameters
     * @param int $code HTTP status code
     */
    function setRedirectRoute($route, array $parameters = array(), $code = Codes::HTTP_FOUND);

    /**
     * Sets a redirect using an URI
     *
     * @param string $uri URI
     * @param int $code HTTP status code
     */
    function setRedirectUri($uri, $code = Codes::HTTP_FOUND);

    /**
     * Sets target location to use when recreating a response
     *
     * @param string $location target uri
     *
     * @throws \InvalidArgumentException if the location is empty
     */
    function setLocation($location);

    /**
     * Gets target to use for creating the response
     *
     * @return string target uri
     */
    function getLocation();

    /**
     * Sets a response HTTP status code
     *
     * @param int $code optional http status code
     */
    function setStatusCode($code);

    /**
     * Sets the response HTTP status code for a failed validation
     */
    function setFailedValidationStatusCode();

    /**
     * Gets a response HTTP status code
     *
     * @return int HTTP status code
     */
    function getStatusCode();

    /**
     * Sets the key for a FormInstance in the parameters
     *
     * @param string    $key   key that points to a FormInstance inside the parameters
     */
    function setFormKey($key);

    /**
     * Sets to be encoded parameters
     *
     * @param string|array|object $parameters parameters to be used in the encoding
     */
    function setParameters($parameters);

    /**
     * Gets to be encoded parameters
     *
     * @return string|array|object parameters to be used in the encoding
     */
    function getParameters();

    /**
     * Sets template to use for the encoding
     *
     * @param string|TemplateReference $template template to be used in the encoding
     *
     * @throws \InvalidArgumentException if the template is neither a string nor an instance of TemplateReference
     */
    function setTemplate($template);

    /**
     * Gets template to use for the encoding
     *
     * When the template is an array this method
     * ensures that the format and engine are set
     *
     * @return string|TemplateReference template to be used in the encoding
     */
    function getTemplate();

    /**
     * Sets engine to use for the encoding
     *
     * @param string $engine engine to be used in the encoding
     */
    function setEngine($engine);

    /**
     * Gets engine to use for the encoding
     *
     * @return string engine to be used in the encoding
     */
    function getEngine();

    /**
     * Sets encoding format
     *
     * @param string $format format to be used in the encoding
     */
    function setFormat($format);

    /**
     * Gets encoding format
     *
     * @return string format to be used in the encoding
     */
    function getFormat();

    /**
     * Set the serializer service
     *
     * @param SerializerInterface $serializer a serializer instance
     */
    function setSerializer(SerializerInterface $serializer = null);

    /**
     * Get the serializer service
     *
     * @return SerializerInterface
     */
    function getSerializer();

    /**
     * Handles a request with the proper handler
     *
     * Decides on which handler to use based on the request format
     *
     * @param Request $request Request object
     * @param Response $response optional response object to use
     *
     * @return Response
     */
    function handle(Request $request = null, Response $response = null);
}

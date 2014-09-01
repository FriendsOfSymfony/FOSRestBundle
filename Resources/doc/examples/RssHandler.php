<?php

namespace FOS\RestBundle\Examples;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * This is an example RSS ViewHandler.
 * It also shows how to handle exceptions within the ViewHandler so that the
 * client can get a decent response.
 *
 * Please note that you will need to install the Zend library to use this
 * handler.
 *
 * Configuration:
 *
 * services:
 *   my.rss_handler:
 *     class: FOS\RestBundle\Examples\RssHandler
 *     arguments:
 *       logger: "@?logger"
 *
 *   my.view_handler:
 *     parent: fos_rest.view_handler.default
 *     calls:
 *      - ['registerHandler', [ 'rss', ["@my.rss_handler", 'createResponse'] ] ]
 *
 * fos_rest:
 *   service:
 *     view_handler: my.view_handler
 *
 * @author Tarjei Huse (tarjei - at scanmine.com)
 */
class RssHandler
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Converts the viewdata to a RSS feed. Modify to suit your datastructure.
     * @return Response
     */
    public function createResponse(ViewHandler $handler, View $view, Request $request)
    {
        try {
            $content = $this->createFeed($view->getData());
            $code = Codes::HTTP_OK;
        } catch (\Exception $e) {
            if ($this->logger) {
                $this->logger->error($e);
            }

            $content = sprintf("%s:<br/><pre>%s</pre>", $e->getMessage(), $e->getTraceAsString());
            $code = Codes::HTTP_BAD_REQUEST;
        }

        return new Response($content, $code, $view->getHeaders());
    }

    /**
     * @param $data array
     * @param format string, either rss or atom
     */
    protected function createFeed($data, $format = "rss")
    {
        $feed = new \Zend_Feed_Writer_Feed();
        $feed->setTitle($data['title']);
        $feed->setLink($data['link']);
        $feed->setFeedLink($data['link'], 'rss');
        $feed->addAuthor(array(
            'name'  => 'ZeroCMS',
            'email' => 'email!',
        ));
        $feed->setDateModified(time());
        $feed->setDescription("RSS feed from query");

        // Add one or more entries. Note that entries must be manually added once created.
        foreach ($data['documents'] as $document) {
            $entry = $feed->createEntry();

            $entry->setTitle($document['title']);
            $entry->setLink($document['url']);
            $entry->addAuthor(array(
                'name'  => $document['author'],
                //'email' => '',
                //'uri'   => '',
            ));

            $entry->setDateModified($document['dateUpdated']->getTimestamp());
            $entry->setDateCreated($document['dateCreated']->getTimestamp());

            if (isset($document['summary'])) {
                $entry->setDescription($document['summary']);
            }

            $entry->setContent($document['body'] );
            $feed->addEntry($entry);
        }

        return $feed->export($format);
    }
}

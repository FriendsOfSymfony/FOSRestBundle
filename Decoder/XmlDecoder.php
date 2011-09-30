<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Decoder;

/**
 * Decodes XML data
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author John Wards <jwards@whiteoctober.co.uk>
 * @author Fabian Vogler <fabian@equivalence.ch>
 */
class XmlDecoder implements DecoderInterface
{
    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        $xml = @simplexml_load_string($data);
        if (!$xml) {
            return;
        }

        if (!$xml->count()) {
            if (!$xml->attributes()) {
                return (string) $xml;
            }
            $data = array();
            foreach ($xml->attributes() as $attrkey => $attr) {
                $data['@'.$attrkey] = (string) $attr;
            }
            $data['#'] = (string) $xml;

            return $data;
        }

        return $this->parseXml($xml);
    }

    /**
     * Parse the input SimpleXmlElement into an array
     *
     * @param \SimpleXmlElement $node xml to parse
     * @return array
     */
    private function parseXml(\SimpleXmlElement $node)
    {
        $data = array();
        if ($node->attributes()) {
            foreach ($node->attributes() as $attrkey => $attr) {
                $data['@'.$attrkey] = (string) $attr;
            }
        }
        foreach ($node->children() as $key => $subnode) {
            if ($subnode->count()) {
                $value = $this->parseXml($subnode);
            } elseif ($subnode->attributes()) {
                $value = array();
                foreach ($subnode->attributes() as $attrkey => $attr) {
                    $value['@'.$attrkey] = (string) $attr;
                }
                $value['#'] = (string) $subnode;
            } else {
                $value = (string) $subnode;
            }

            if ($key === 'item') {
                if (isset($value['@key'])) {
                    $data[(string)$value['@key']] = $value['#'];
                } elseif (isset($data['item'])) {
                    $tmp = $data['item'];
                    unset($data['item']);
                    $data[] = $tmp;
                    $data[] = $value;
                }
            } elseif (array_key_exists($key, $data)) {
                if ((false === is_array($data[$key]))  || (false === isset($data[$key][0]))) {
                    $data[$key] = array($data[$key]);
                }
                $data[$key][] = $value;
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}

<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Decoder;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

/**
 * Decodes XML data
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author John Wards <jwards@whiteoctober.co.uk>
 * @author Fabian Vogler <fabian@equivalence.ch>
 */
class XmlDecoder implements DecoderInterface
{
    private $encoder;

    public function __construct()
    {
        $this->encoder = new XmlEncoder();
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        return $this->encoder->decode($data, 'xml');
    }
}

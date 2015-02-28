<?php

return array(
    new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
    new \JMS\SerializerBundle\JMSSerializerBundle(),
    new \FOS\RestBundle\FOSRestBundle(),
    new \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\TestBundle()
);

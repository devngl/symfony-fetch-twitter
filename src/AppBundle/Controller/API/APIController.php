<?php

namespace AppBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;

class APIController extends FOSRestController
{
    protected function serialize($data)
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return $this->container->get('jms_serializer')
            ->serialize($data, 'json', $context);
    }
}
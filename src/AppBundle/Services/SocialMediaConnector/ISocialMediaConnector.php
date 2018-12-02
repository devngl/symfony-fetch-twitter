<?php

namespace AppBundle\Services\SocialMediaConnector;

interface ISocialMediaConnector
{
    /**
     * Fetch a determinate number of messages of a given user
     * @param string $username
     * @param int $quantity
     */
    public function fetchMessages(string $username, int $quantity);
}
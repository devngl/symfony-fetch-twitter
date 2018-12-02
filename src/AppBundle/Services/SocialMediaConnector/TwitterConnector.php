<?php

namespace AppBundle\Services\SocialMediaConnector;

class TwitterConnector implements ISocialMediaConnector
{
    private $settings;

    /* @var \TwitterAPIExchange */
    private $APIExchange;

    public function __construct(array $twitterSettings)
    {
        $this->settings = $twitterSettings;
        $this->APIExchange = new \TwitterAPIExchange($this->settings);
    }

    /**
     * Fetch a determinate number of messages of a given user
     * @param string $username
     * @param int $quantity
     * @return array
     * @throws \Exception
     */
    public function fetchMessages(string $username, int $quantity)
    {
        $url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
        $method = 'GET';
        $getField = "?screen_name={$username}&count={$quantity}";

        return json_decode($this->APIExchange->setGetfield($getField)
            ->buildOauth($url, $method)
            ->performRequest(), true);
    }
}
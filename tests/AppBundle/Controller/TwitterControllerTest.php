<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TwitterControllerTest extends WebTestCase
{
    private const EXISTING_USERNAME = 'twitter';

    /**
     * @param string $username
     * @param int $quantity
     * @return Client
     */
    private function requestUserTimeline($username = self::EXISTING_USERNAME, $quantity = 10): Client
    {
        $client = static::createClient();

        $client->request('GET', "/api/users/{$username}/tweets/{$quantity}");
        return $client;
    }

    /**
     * Code 412 is received when form is not valid
     * @test
     */
    function request_quantity_must_be_integer_greater_than_zero()
    {
        $response = $this->requestUserTimeline(self::EXISTING_USERNAME, 0)->getResponse();
        $this->assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
        $this->assertTrue(is_array(json_decode($response->getContent())));

        $response = $this->requestUserTimeline(self::EXISTING_USERNAME, 'not_a_number')->getResponse();
        $this->assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
        $this->assertTrue(is_array(json_decode($response->getContent())));
    }

    /**
     * @test
     */
    function request_username_must_be_under_sixteen_characters()
    {
        $invalidUsername = 'ABCABCABCABCABCA';
        $response = $this->requestUserTimeline($invalidUsername, 10)->getResponse();
        $this->assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
        $this->assertTrue(is_array(json_decode($response->getContent())));
    }
}

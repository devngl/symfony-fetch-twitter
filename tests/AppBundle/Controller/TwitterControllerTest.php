<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TwitterControllerTest extends WebTestCase
{
    private const EXISTING_USERNAME = 'twitter';

    /**
     * @test
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function can_get_tweets_from_an_existing_user()
    {
        $this->clearCachedKey();
        $client = $this->requestUserTimeline();
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent());
        $this->assertTrue(is_array($responseData));

        $firstTweet = $responseData[0];
        $this->assertObjectHasAttribute('created_at', $firstTweet);
        $this->assertObjectHasAttribute('text', $firstTweet);
    }

    /**
     * @param string $username
     * @param int $quantity
     * @throws \Psr\Cache\InvalidArgumentException
     */
    private function clearCachedKey($username = self::EXISTING_USERNAME, $quantity = 10)
    {
        $container = static::createClient()->getContainer();
        $cacheKey = "tweets_{$username}_{$quantity}";
        $container->get('cache.app')->deleteItem($cacheKey);
    }

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
     * @test
     */
    public function request_respects_indicated_quantity_limit()
    {
        $client = $this->requestUserTimeline(self::EXISTING_USERNAME, 5);
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent());
        $this->assertLessThanOrEqual(5, \count($responseData));
    }

    /** @test
     * @throws \Exception
     */
    public function errors_are_received_when_user_does_not_exist()
    {
        // Since admin keyword is reserved to Twitter admins, this is likely non-existent (I hope)
        $nonExistingUser = '____admin____';
        $client = $this->requestUserTimeline($nonExistingUser);
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent());
        $this->assertObjectHasAttribute('errors', $responseData);
        $firstError = $responseData->errors[0];
        $this->assertObjectHasAttribute('code', $firstError);
        $this->assertObjectHasAttribute('message', $firstError);
    }

    /**
     * @test
     */
    public function tweets_are_in_uppercase()
    {
        $client = $this->requestUserTimeline();
        $response = $client->getResponse();

        $responseData = json_decode($response->getContent());
        $firstTweet = $responseData[0];

        $this->assertEquals(mb_strtoupper($firstTweet->text), $firstTweet->text);
    }

    /**
     * Code 412 is received when form is not valid
     * @test
     */
    public function request_quantity_must_be_integer_greater_than_zero()
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
    public function request_username_must_be_under_sixteen_characters()
    {
        $invalidUsername = 'ABCABCABCABCABCA';
        $response = $this->requestUserTimeline($invalidUsername, 10)->getResponse();
        $this->assertEquals(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
        $this->assertTrue(is_array(json_decode($response->getContent())));
    }
}

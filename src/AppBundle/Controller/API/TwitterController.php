<?php

namespace AppBundle\Controller\API;

use AppBundle\Controller\API\Exceptions\TwitterConnectorException;
use AppBundle\Services\SocialMediaConnector\TwitterConnector;
use AppBundle\Utils\Cache;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

class TwitterController extends APIController
{
    // Cache lifespan in minutes
    private const CACHE_LIFESPAN = 15;

    /** @var TwitterConnector */
    private $twitterConnector;

    /** @var Cache */
    private $cache;

    public function __construct(TwitterConnector $twitterConnector, Cache $cache)
    {
        $this->twitterConnector = $twitterConnector;
        $this->cache = $cache;
    }

    /**
     * @param $userName
     * @param int $quantity
     * @return string
     * @throws \Exception
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getUserTweetsAction($userName, $quantity = 10)
    {
        $this->validateRequest($userName, $quantity);

        $cacheKey = "tweets_{$userName}_{$quantity}";
        $tweets = $this->cache
            ->remember($cacheKey, self::CACHE_LIFESPAN, function () use ($userName, $quantity) {
                $twitterResponse = $this->twitterConnector->fetchMessages($userName, $quantity);

                if (\array_key_exists('errors', $twitterResponse)) {
                    throw new TwitterConnectorException(json_encode($twitterResponse), 500);
                }

                return array_map([$this, 'formatTweet'], $twitterResponse);
            });

        return new Response($this->serialize($tweets), Response::HTTP_OK);
    }

    /**
     * @param $tweet
     * @return array
     * @throws \Exception
     */
    private function formatTweet($tweet)
    {
        return [
            'created_at' => new \DateTime($tweet['created_at']),
            'text' => mb_strtoupper($tweet['text']),
        ];
    }

    /**
     * @param $userName
     * @param $quantity
     */
    private function validateRequest($userName, $quantity): void
    {
        $collectionConstraint = new Collection([
            'username' => [
                new NotBlank(),
                new Length([
                    'min' => 1,
                    'max' => 15
                ]),
            ],
            'quantity' => [
                new NotBlank(),
                new GreaterThan(['value' => 0])
            ],
        ]);

        $validator = $this->container->get('validator');
        $errors = $validator->validate([
            'username' => $userName,
            'quantity' => $quantity,
        ], $collectionConstraint);

        if ($errors->count()) {
            throw new InvalidArgumentException($this->serialize($errors), Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
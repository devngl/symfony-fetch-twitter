<?php

namespace AppBundle\Controller\API;

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
     */
    public function getUserTweetsAction($userName, $quantity = 10)
    {
        $this->validateRequest($userName, $quantity);
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
<?php

namespace AppBundle\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class ExceptionController extends Controller
{
    public function show(\Exception $exception)
    {
        return new JsonResponse(json_decode($exception->getMessage()), $exception->getCode());
    }
}
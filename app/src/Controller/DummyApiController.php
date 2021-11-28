<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DummyApiController extends AbstractController
{

    /**
     * @return Response
     */
    public function dummyExternalAPI(): Response
    {
        return new Response();
    }
}
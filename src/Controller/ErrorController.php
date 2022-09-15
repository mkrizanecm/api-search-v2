<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ErrorController extends AbstractController
{
    public function error (Request $response)
    {   
        $result = new Response(json_encode([
            "data" => [
                "message" => "An error ocurred.",
            ],
        ]));

        $result->headers->set("Content-Type", "application/vnd.api+json");
        $result->setStatusCode(404);

        return $result;
    } 
}
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Term;

class ApiController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"}, name="index")
     */
    public function index (Request $response)
    {   
        return $this->redirectToRoute("api");
    } 
    
    /**
     * @Route("/api", methods={"GET"}, name="api")
     */
    public function api (Request $response)
    {
        $result = new Response(json_encode([
            "data" => [
                "message" => "Welcome to Search Terms API. Search terms example: /api/github/{term}",
                "example" => "/api/github/term",
            ],
        ]));

        $result->headers->set("Content-Type", "application/vnd.api+json");
        $result->setStatusCode(200);

        return $result;
    }

    /**
     * @Route("/api/{provider}", methods={"GET"}, name="provider")
     */
    public function provider (string $provider, Request $response)
    {
        $provider = trim($provider);

        $checkProvider = $this->getDoctrine()
        ->getRepository("App\Entity\Provider")
        ->findOneBy([
            'ident' => $provider,
        ]);

        if (!empty($checkProvider)) 
        {
            $result = new Response(json_encode([
                "data" => [
                    "message" => "Search terms example for {$provider}",
                    "example" => "/api/{$provider}/term",
                ],
            ]));
        }
        else 
        {
            $result = new Response(json_encode([
                "data" => [
                    "message" => "Provider doesn't exist.",
                ],
            ]));
        }
        
        $result->headers->set("Content-Type", "application/vnd.api+json");
        $result->setStatusCode(200);

        return $result;
    }

    /**
     * @Route("/api/{provider}/{term}", methods={"GET"}, name="search")
     */
    public function search (string $provider, string $term, Request $response)
    {
        $term = trim($term);
        $provider = trim($provider);

        if (empty($term)) 
        {
            $result = new Response(json_encode([
                "data" => [
                    "message" => "Provider cannot be empty.",
                ],
            ]));
        } 
        else if (empty($provider)) 
        {
            $result = new Response(json_encode([
                "data" => [
                    "message" => "Term cannot be empty.",
                ],
            ]));
        } 
        else 
        {
            $checkProvider = $this->getDoctrine()
            ->getRepository("App\Entity\Provider")
            ->findOneBy([
                'ident' => $provider,
            ]);

            if (!empty($checkProvider)) 
            {
                $checkTerm = $this->getDoctrine()
                ->getRepository("App\Entity\Term")
                ->findOneBy([
                    'term'     => $term,
                    'provider' => $provider
                ]);

                if (!empty($checkTerm)) 
                {
                    $result = new Response(json_encode([
                        "data" => [
                            "term"  => $term,
                            "score" => (float)$checkTerm->getScore(),
                        ],
                    ]));
                } 
                else 
                {
                    $score = $this->$provider($checkProvider->getUrl().$term);

                    $this->saveTerm($term, $score, $provider);

                    $result = new Response(json_encode([
                        "data" => [
                            "term"  => $term,
                            "score" => (float)$score,
                        ],
                    ]));
                }
            } 
            else 
            {
                $result = new Response(json_encode([
                    "data" => [
                        "message" => "Provider doesn't exist.",
                    ],
                ]));
            }
        }

        $result->headers->set("Content-Type", "application/vnd.api+json");
        $result->setStatusCode(200);

        return $result;
    }

    public function saveTerm ($term, $score, $provider) 
    {
        $entity = $this->getDoctrine()->getManager();

        $record = new Term();

        $record->setTerm($term);
        $record->setScore($score);
        $record->setProvider($provider);
        $record->setCreated(new \DateTime('@'.strtotime('now')));

        $entity->persist($record);
        $entity->flush();
    }

    public function github ($url)
    {
        $clientId = $this->getParameter('github.client.id');
        $clientSecret = $this->getParameter('github.client.secret');

        $termsPositive = $this->request($url.'+rocks', $clientId, $clientSecret);
        $termsNegative = $this->request($url.'+sucks', $clientId, $clientSecret);
        
        $totalResults = $termsPositive["total_count"] + $termsNegative["total_count"];
        
        if (!empty($totalResults)) 
        {
            $score = 100 - (1 - $termsPositive["total_count"] / $totalResults) * 100;
            $score = number_format($score / 10, 2, '.', '');
        } 
        else 
        {
            $score = 0;
        }

        return $score;
    }

    /*
     Each provider returns different set of results
     so different function are created 
    */
    public function twitter ($url)
    {
        $clientId = $this->getParameter('twitter.client.id');
        $clientSecret = $this->getParameter('twitter.client.secret');

        $score = 0;

        /* 
         Logic here
        */

        return $score;
    }

    public function request ($url, $client, $secret)
    {
        $curl = curl_init();

        $headers['Authorization'] = "Basic ".base64_encode($client.":".$secret);

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        return json_decode($response, true);
    }
}
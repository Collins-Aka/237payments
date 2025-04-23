<?php

namespace Larrytech\237payments\MtnMomo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Larrytech\PaymentGatewayInterface;

class MtnMomoPaymentGateway implements PaymentGatewayInterface
{
    private $apiKey; // your personal API key
    private $apiSecret; // secret key given to you by MTN
    private $baseUrl; // URL of the MTN MoMo API

    public function __construct($apiKey, $apiSecret, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseUrl = $baseUrl; // Fixed typo from $baseurl to $baseUrl
    }

    public function requestPayment($amount, $currency, $payer, $payee)
    {
        $client = new Client();
        $targetEnvironment = 'sandbox'; // or 'live'

        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(), // Added missing space after 'Bearer'
            'Content-Type' => 'application/json',
            'X-Target-Environment' => $targetEnvironment,
            'Ocp-Apim-Subscription-Key' => $this->apiKey,
        ];

        $body = [
            'amount' => $amount,
            'currency' => $currency,
            'externalId' => uniqid(),
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $payer,
            ],
            'payee' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $payee,
            ],
            'payerMessage' => 'Payment request',
            'payeeNote' => 'Payment request',
        ];

        try {
            $response = $client->post($this->baseUrl . '/collection/v1_0/requesttopay', [
                'headers' => $headers,
                'json' => $body,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            return $responseData;
        } catch (RequestException $e) {
            // Added error handling for HTTP requests
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ];
        }
    }

    private function getAccessToken()
    {
        $environment = 'sandbox'; // or 'live'
        $baseUrl = ($environment == 'sandbox') ? 'https://sandbox.momodeveloper.mtn.com' : 'https://live.momodeveloper.mtn.com';

        $client = new Client();
        try {
            $response = $client->post($baseUrl . '/collection/token/', [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->apiKey . ':' . $this->apiSecret),
                    'Ocp-Apim-Subscription-Key' => $this->apiKey,
                ],
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            return $responseData['access_token'];
        } catch (RequestException $e) {
            // Added error handling for token retrieval
            throw new \Exception('Failed to retrieve access token: ' . $e->getMessage());
        }
    }
}

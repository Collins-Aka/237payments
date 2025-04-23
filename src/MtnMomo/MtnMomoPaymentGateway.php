<?php

namespace Larrytech\Mboapayments\MtnMomo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Larrytech\Mboapayments\PaymentGatewayInterface;

use Dotenv\Dotenv; // Import Dotenv


class MtnMomoPaymentGateway implements PaymentGatewayInterface
{
    private $apiKey;
    private $apiSecret;
    private $baseUrl;

    public function __construct()
    {
        // Load environment variables
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../..');
        $dotenv->load();

        $this->apiKey = $_ENV['API_KEY'];
        $this->apiSecret = $_ENV['API_SECRET'];
        $this->baseUrl = $_ENV['BASE_URL'];
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

    public function verifyPayment($transactionId)
    {
        $client = new Client();
        $targetEnvironment = 'sandbox'; // or 'live'

        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
            'X-Target-Environment' => $targetEnvironment,
            'Ocp-Apim-Subscription-Key' => $this->apiKey,
        ];

        try {
            $response = $client->get($this->baseUrl . '/collection/v1_0/requesttopay/' . $referenceId, [
                'headers' => $headers,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            return $responseData;
        } catch (RequestException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ];
        }
    }

    public function refundPayment($transactionId, $amount, $currency) 
    {
        $client = new Client();
        $targetEnvironment = 'sandbox'; // or 'live'

        $headers = [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
            'X-Target-Environment' => $targetEnvironment,
            'Ocp-Apim-Subscription-Key' => $this->apiKey,
        ];

        $body = [
            'amount' => $amount,
            'currency' => $currency,
            'externalId' => uniqid(),
            'payerMessage' => 'Refund request',
            'payeeNote' => 'Refund request',
        ];

        try {
            $response = $client->post($this->baseUrl . '/refund/v1_0/transfer/' . $transactionId, [
                'headers' => $headers,
                'json' => $body,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            return $responseData;
        } catch (RequestException $e) {
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

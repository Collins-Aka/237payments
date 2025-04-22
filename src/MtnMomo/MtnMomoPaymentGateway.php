<?php

namespace Larrytech\237payments\MtnMomo;

use GuzzleHttp\Client;
use Larrytech\PaymentGatewayInterface;

class MtnMomoPaymentGateway implements PaymentGatewayInterface
{
    private $apiKey;//your personal api key either personal or business (user identificaion api) (subscription key)
    private $apiSecret;//secret key given to you by MTN, used for authentication and signing requests.
    private $baseUrl;//URL of the MTN MoMo API

    public function __construct($apiKey, $apiSecret, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->baseurl =$baseUrl;
    }

    public function requestPayment($amount, $currency, $payer, $payee)
    {
        $client = new Client();

        $headers = [
            'Authorization' => 'Bearer' . $this->getAccessToken(),
            'Content-Type' => 'application/json',
            'X-Target-Environment' => 'sandbox',
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

        $response = $client->post($this->baseUrl . '/collection/v1_0/requesttopay', [
            'headers' => $headers,
            'json' => $body,
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        return $responseData;
    }
    
    private function getAccessToken()
    {
        // implement logic to get access token using client credentials flow
    }
   
}

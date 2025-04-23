<?php

namespace Larrytech\Mboapayments;

interface PaymentGatewayInterface
{
    public function requestPayment($amount, $currency, $payer, $payee);
    public function verifyPayment($referenceId);
    public function refundPayment($referenceId);
}
<?php

namespace Larrytech\237payments;

interface PaymentGatewayInterface
{
    public function requestPayment($amount, $currency, $payer, $payee);
    public function verifyPayment($referenceId);
    public function refundPayment($referenceId);
}
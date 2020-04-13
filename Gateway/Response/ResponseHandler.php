<?php

namespace Payload\PayloadMagento\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class ResponseHandler implements HandlerInterface {

    public function handle(array $handlingSubject, array $response) {
        $payment = $handlingSubject['payment']->getPayment();
        $payment->setTransactionId(substr($response["response"]->status, 0, 1).$response["response"]->id);
        $payment->setIsTransactionClosed(false);
    }
}

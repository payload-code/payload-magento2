<?php

namespace Payload\PayloadMagento\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class AuthorizeRequestDataBuilder implements BuilderInterface {
    private $config;

    public function __construct(ConfigInterface $config) {
        $this->config = $config;
    }

    public function build(array $buildSubject) {
        $payment = $buildSubject['payment']->getPayment();
        $order   = $buildSubject['payment']->getOrder();

        return [
            'payment' => [
                'id' => $payment->getAdditionalInformation('transaction_id'),
                'status' => 'authorized',
                'order_number' => $order->getOrderIncrementId(),
                'amount' => $order->getGrandTotalAmount()
            ],
            'api_key' => $this->config->getValue('payload_secret_key', $order->getStoreId())
        ];
    }
}

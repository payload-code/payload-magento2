<?php

namespace Payload\PayloadMagento\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

class ProcessRequestDataBuilder implements BuilderInterface {
    private $config;

    public function __construct(ConfigInterface $config) {
        $this->config = $config;
    }

    public function build(array $buildSubject) {
        $payment = $buildSubject['payment']->getPayment();

        $txn_id = explode(':',$payment->getLastTransId());
        return [
            'payment' => [
                'id' => end($txn_id),
                'status' => 'processed',
            ],
            'api_key' => $this->config->getValue('payload_secret_key', $order->getStoreId())
        ];
    }
}

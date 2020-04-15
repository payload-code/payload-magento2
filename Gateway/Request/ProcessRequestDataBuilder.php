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
        $order   = $buildSubject['payment']->getOrder();

        $txn_id = explode(':',$payment->getLastTransId());

        $req = [
            'payment' => [
                'id' => end($txn_id),
                'status' => 'processed',
            ],
            'api_key' => $this->config->getValue('payload_secret_key', $order->getStoreId())
        ];

        if ( $this->config->getValue('payload_processing_id', $order->getStoreId()) )
            $req['payment']['processing_id'] = $this->config->getValue('payload_processing_id', $order->getStoreId());

        return $req;
    }
}

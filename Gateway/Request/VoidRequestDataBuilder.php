<?php

namespace Payload\PayloadMagento\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class VoidRequestDataBuilder implements BuilderInterface {
    private $config;

    protected $encryptor;

    public function __construct(EncryptorInterface $encryptor, ConfigInterface $config) {
        $this->encryptor = $encryptor;
        $this->config = $config;
    }

    public function build(array $buildSubject) {
        $payment = $buildSubject['payment']->getPayment();
        $order   = $buildSubject['payment']->getOrder();

        $txn_id = explode(':',$payment->getLastTransId());

        $req = [
            'payment' => [
                'id' => end($txn_id),
                'status' => 'voided',
            ],
            'api_key' => $this->config->getValue('payload_secret_key', $order->getStoreId()),
            'store_token' => false
        ];


        if ( $this->config->getValue('payload_processing_id', $order->getStoreId()) )
            $req['payment']['processing_id'] = $this->getProcessingID($order);

        return $req;
    }

    public function getProcessingID($order) {
        $key = $this->config->getValue('payload_processing_id', $order->getStoreId());
        return $this->encryptor->decrypt($key);
    }
}

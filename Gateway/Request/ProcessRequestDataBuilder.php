<?php

namespace Payload\PayloadMagento\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Encryption\EncryptorInterface;

class ProcessRequestDataBuilder implements BuilderInterface {
    private $config;
    private $logger;

    protected $encryptor;

    public function __construct(Logger $logger, EncryptorInterface $encryptor, ConfigInterface $config) {
        $this->encryptor = $encryptor;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function build(array $buildSubject) {
        $payment = $buildSubject['payment']->getPayment();
        $order   = $buildSubject['payment']->getOrder();

        if ( $payment->getAdditionalInformation('transaction_id') )
            $txn_id = $payment->getAdditionalInformation('transaction_id');
        else
            $txn_id = end(explode(':',$payment->getLastTransId()));

        $req = [
            'payment' => [
                'id' => $txn_id,
                'status' => 'processed',
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

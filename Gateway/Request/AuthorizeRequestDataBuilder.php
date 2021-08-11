<?php

namespace Payload\PayloadMagento\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Encryption\EncryptorInterface;

class AuthorizeRequestDataBuilder implements BuilderInterface {
    private $config;
    private $logger;

    protected $encryptor;

    public function __construct(Logger $logger, EncryptorInterface $encryptor, ConfigInterface $config) {
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->config = $config;
    }

    public function build(array $buildSubject) {
        $payment = $buildSubject['payment']->getPayment();
        $order   = $buildSubject['payment']->getOrder();
        $extensionAttributes = $payment->getExtensionAttributes();
        $paymentToken = $extensionAttributes->getVaultPaymentToken();

        $req = [
            'payment' => [
                'type' => 'payment',
                'status' => 'authorized',
                'order_number' => $order->getOrderIncrementId(),
                'amount' => $order->getGrandTotalAmount()
            ],
            'api_key' => $this->config->getValue('payload_secret_key', $order->getStoreId()),
            'store_token' => $payment->getAdditionalInformation('is_active_payment_token_enabler')
        ];

        if ( $payment->getAdditionalInformation('transaction_id') )
            $req['payment']['id'] = $payment->getAdditionalInformation('transaction_id');

        if ( $paymentToken )
            $req['payment']['payment_method_id'] = $paymentToken->getGatewayToken();

        if ( $this->config->getValue('payload_processing_id', $order->getStoreId()) )
            $req['payment']['processing_id'] = $this->getProcessingID($order);

        return $req;
    }

    public function getProcessingID($order) {
        $key = $this->config->getValue('payload_processing_id', $order->getStoreId());
        return $this->encryptor->decrypt($key);
    }
}

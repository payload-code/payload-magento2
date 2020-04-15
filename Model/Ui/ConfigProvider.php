<?php

namespace Payload\PayloadMagento\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payload';
    const PL_CLIENT_KEY = 'payment/payload/payload_client_key';
    const PL_SECRET_KEY = 'payment/payload/payload_secret_key';
    const PL_PROCESSING_ID = 'payment/payload/payload_processing_id';

    protected $encryptor;
    protected $scopeConfig;

    public function __construct(
        EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'client_key' => $this->getClientKey(),
                    'processig_id' => $this->getProcessingID()
                ]
            ]
        ];
    }

    public function getClientKey()
    {
        $key = $this->scopeConfig->getValue(self::PL_CLIENT_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

    public function getProcessingID()
    {
        $key = $this->scopeConfig->getValue(self::PL_PROCESSING_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

}

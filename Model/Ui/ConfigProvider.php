<?php

namespace Payload\PayloadMagento\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payload';
    const PL_CLIENT_KEY = 'payment/payload/payload_client_key';

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
                    'payloadClientKey' => $this->getClientKey()
                ]
            ]
        ];
    }

    public function getClientKey()
    {
        $key = $this->scopeConfig->getValue(self::PL_CLIENT_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }
}

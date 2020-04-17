<?php

namespace Payload\PayloadMagento\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Payload\API as pl;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payload';
    const VAULT_CODE = 'payload_vault';
    const VAULT_ENABLED = 'payment/payload_vault/active';
    const PL_CLIENT_KEY = 'payment/payload/payload_client_key';
    const PL_SECRET_KEY = 'payment/payload/payload_secret_key';
    const PL_PROCESSING_ID = 'payment/payload/payload_processing_id';

    protected $encryptor;
    protected $scopeConfig;

    public function __construct(
        EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession
    ){
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
    }

    public function getConfig() {

        pl::$api_key = $this->getPrivateKey();

        $data = [
            'payment' => [
                self::CODE => [
                    'client_key' => $this->getClientKey(),
                    'processing_id' => $this->getProcessingID(),
                    'ccVaultCode' => self::VAULT_CODE
                ]
            ]
        ];

        $cust_id = $this->getCustId();
        if ( $cust_id !== null ) {
            $data['payment'][self::CODE]['customer_id'] = $cust_id;
        }

        return $data;
    }

    public function getClientKey() {
        $key = $this->scopeConfig->getValue(self::PL_CLIENT_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

    public function getPrivateKey() {
        $key = $this->scopeConfig->getValue(self::PL_SECRET_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

    public function getProcessingID() {
        $key = $this->scopeConfig->getValue(self::PL_PROCESSING_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

    public function getCustId() {
        if ( !$this->scopeConfig->getValue(self::VAULT_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) )
            return null;

        $M_cust = $this->customerSession->getCustomer();
        if ( is_null($M_cust->getId()) )
            return null;

        $customers = \Payload\Account::filter_by(
            pl::attr()->attrs->magento_customer->eq($M_cust->getId())
        )->all();

        if (count($customers))
            $cust = $customers[0];
        else
            $cust = \Payload\Account::create([
                'email'=>$M_cust->getEmail(),
                'name'=>$M_cust->getName(),
                'type'=>'customer',
                'attrs'=>[
                    'magento_customer' => $M_cust->getId()
                ]
            ]);

        return $cust->id;
    }

}

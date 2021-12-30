<?php

namespace Payload\PayloadMagento\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Payload\API as pl;

class PayloadCcConfigProvider implements ConfigProviderInterface
{
    const CODE = 'payload';
    const VAULT_CODE = 'payload_vault';
    const VAULT_ENABLED = 'payment/payload_vault/active';
    const PL_SECRET_KEY = 'payment/payload/payload_secret_key';
    const PL_PROCESSING_ID = 'payment/payload/payload_processing_id';
    const CARDS_ENABLED = 'payment/payload/payload_cards_enabled';
    const ACH_ENABLED = 'payment/payload/payload_ach_enabled';

    protected $code = self::CODE;
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

        $client_key = \Payload\ClientToken::create([]);

        $data = [
            'payment' => [
                $this->code => [
                    'client_key' => $client_key->id,
                    'processing_id' => $this->getProcessingID(),
                    'cards_enabled' => $this->getCardsEnabled(),
                    'ach_enabled' => $this->getACHEnabled(),
                    'ccVaultCode' => self::VAULT_CODE
                ]
            ]
        ];

        $cust_id = $this->getCustId();
        if ( $cust_id !== null ) {
            $data['payment'][$this->code]['customer_id'] = $cust_id;
        }

        return $data;
    }

    public function getPrivateKey() {
        $key = $this->scopeConfig->getValue(self::PL_SECRET_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

    public function getProcessingID() {
        $key = $this->scopeConfig->getValue(self::PL_PROCESSING_ID, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $this->encryptor->decrypt($key);
    }

    public function getCardsEnabled() {
        return $this->scopeConfig->getValue(self::CARDS_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getACHEnabled() {
        return $this->scopeConfig->getValue(self::ACH_ENABLED, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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

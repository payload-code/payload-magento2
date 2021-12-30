<?php

namespace Payload\PayloadMagento\Observer;

use Magento\Framework\Event\ObserverInterface;
use Payload\PayloadMagento\Model\Ui\PayloadApplePayConfigProvider;
use Payload\PayloadMagento\Model\Ui\PayloadGooglePayConfigProvider;

class PaymentMethodActiveObserver implements ObserverInterface {

    const AREA_CODE = \Magento\Framework\App\Area::AREA_ADMINHTML;

    private $_state;

    public function __construct (
        \Magento\Framework\App\State $state
    ) {
        $this->_state = $state;
    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $code = $observer->getEvent()->getMethodInstance()->getCode();

        if(
            ($code == PayloadApplePayConfigProvider::CODE ||
             $code == PayloadGooglePayConfigProvider::CODE)
            && $this->isAdmin()
        ){
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }

    public function isAdmin()
    {
        $areaCode = $this->_state->getAreaCode();
        return $areaCode == self::AREA_CODE;
    }

}

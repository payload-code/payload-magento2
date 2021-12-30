<?php

namespace Payload\PayloadMagento\Block;

use Magento\Payment\Block\Form\Cc;

class Form extends Cc
{

    public function __construct(
     \Magento\Framework\View\Element\Template\Context $context,
     \Magento\Payment\Model\Config $config,
     \Payload\PayloadMagento\Model\Ui\PayloadCcConfigProvider $confProvider,
     \Magento\Framework\Serialize\Serializer\Json $json
    )
    {
        $this->_conf = $confProvider;
        $this->_json = $json;
        parent::__construct($context, $config);
    }

    public function getConfigJSON() {
        return $this->_json->serialize($this->_conf->getConfig());
    }

    public function getCardsEnabled() {
        return $this->_conf->getCardsEnabled();
    }

    public function getACHEnabled() {
        return $this->_conf->getACHEnabled();
    }

}

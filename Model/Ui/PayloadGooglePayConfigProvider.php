<?php

namespace Payload\PayloadMagento\Model\Ui;

use Magento\Framework\Encryption\EncryptorInterface;
use Payload\PayloadMagento\Model\Ui\PayloadCcConfigProvider;
use Payload\API as pl;

class PayloadGooglePayConfigProvider extends PayloadCcConfigProvider
{
    const CODE = 'payload_googlepay';
    protected $code = self::CODE;
}

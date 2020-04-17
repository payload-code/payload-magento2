<?php

namespace Payload\PayloadMagento\Model\Ui;

use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;
use Payload\PayloadMagento\Model\Ui\ConfigProvider;


class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{

    private $componentFactory;

    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory
    ) {
        $this->componentFactory = $componentFactory;
    }

    public function getComponentForToken(PaymentTokenInterface $paymentToken) {
        $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => ConfigProvider::VAULT_CODE,
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'Payload_PayloadMagento/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}

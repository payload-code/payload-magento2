<?php

namespace Payload\PayloadMagento\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Model\InfoInterface;


class VaultDetailsHandler implements HandlerInterface {

    protected $cc_type = [
            'american_express' => 'AE',
            'discover' => 'DI',
            'mastercard' => 'MC',
            'visa' => 'VI'
        ];

    public function __construct(
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Json $serializer
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->serializer = $serializer;
    }

    public function handle(array $handlingSubject, array $response) {
        $paymentDO = $handlingSubject['payment'];
        $payment = $paymentDO->getPayment();

        if ($response['store_token']) {
            $paymentToken = $this->getVaultPaymentToken($response['response']);
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    protected function getVaultPaymentToken(\Payload\Transaction $payment) {
        $paymentToken = $this->paymentTokenFactory->create(PaymentTokenFactoryInterface::TOKEN_TYPE_CREDIT_CARD);
        $paymentToken->setGatewayToken($payment->payment_method["id"]);
        $paymentToken->setExpiresAt($payment->payment_method["card"]["expiry"]);

        $expiry = date_parse($payment->payment_method["card"]["expiry"]);

        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->cc_type[$payment->payment_method["card"]["card_brand"]],
            'maskedCC' => substr($payment->payment_method["card"]["card_number"],-5),
            'expirationDate' => $expiry["month"]."/".$expiry["year"]
        ]));

        return $paymentToken;
    }

    private function convertDetailsToJSON($details): string {
        $json = $this->serializer->serialize($details);
        return $json ?: '{}';
    }

    private function getExtensionAttributes(InfoInterface $payment): OrderPaymentExtensionInterface {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}

<?php
namespace Payload\PayloadMagento\Model\Payment;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

class Payload extends \Magento\Payment\Model\Method\Adapter
{
    public function acceptPayment(InfoInterface $payment) {
        $payment->setAdditionalInformation('ignore_fraud_threshold', true);
        $capture_skipped = $payment->getAdditionalInformation('capture_skipped');

        if ($capture_skipped)
            parent::capture($payment, $payment->getOrder()->getGrandTotalAmount());

        return true;
    }

    public function denyPayment(InfoInterface $payment) {
        parent::void($payment);
        return true;
    }
}

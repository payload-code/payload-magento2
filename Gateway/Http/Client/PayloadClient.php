<?php

namespace Payload\PayloadMagento\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Framework\Encryption\EncryptorInterface;
use Payload\API as pl;

class PayloadClient implements ClientInterface {

    private $logger;
    private $encryptor;

    public function __construct(Logger $logger, EncryptorInterface $encryptor) {
        $this->logger = $logger;
        $this->encryptor = $encryptor;
    }

    public function placeRequest(TransferInterface $transferObject) {
        $request = $transferObject->getBody();

        pl::$api_key = $this->encryptor->decrypt($request['api_key']);

        try {

            if ( !isset($request['payment']['id']) ) {
                $payment = \Payload\Transaction::create($request['payment']);
            } else {
                $payment = \Payload\Transaction::get($request['payment']['id']);

                if ( isset($request['payment']['order_number'])
                && !$payment->order_number ) {
                    $payment->update([
                        'order_number' => $request['payment']['order_number']
                    ]);
                }

                if ( $request['payment']['status'] == 'processed'
                && $payment->status != 'processed') {
                    $payment->update([
                        'status' => 'processed'
                    ]);
                } else if ( $request['payment']['status'] == 'voided' ) {
                    $payment->update([
                        'status'=> 'voided'
                    ]);
                }
            }

        } catch ( \Payload\Exceptions\NotPermitted $e ) {
            return [
                'error' => 'There is an issue with the configuration of the store'
            ];
        } catch ( \Payload\Exceptions\PayloadError $e ) {
            $this->logger->debug([
                'error' => $e->getMessage(),
                'details' => $e->details,
            ]);
            return [
                'error' => $e->getMessage()
            ];
        }

        $this->logger->debug([
            'request' => $request["payment"],
            'response' => $payment
        ]);

        return array_merge([
            'request' => $request["payment"],
            'store_token' => $request["store_token"],
            'response' => $payment
        ], $this->generateFraudResponse($payment));
    }

    public function generateFraudResponse($payment) {
        if ($payment->risk_score <= -0.4)
            return [
                'FRAUD_MSG_LIST' => [
                    'Suspicious activity',
                ]
            ];

        return [];
    }
}

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

            $payment = \Payload\Transaction::get($request['payment']['id']);

            if ( $request['payment']['status'] == 'authorized' ) {
                $payment->update([
                    'order_number' => $request['payment']['order_number']
                ]);
            } else if ( $request['payment']['status'] == 'processed' ) {
                $payment->update([
                    'status' => 'processed'
                ]);
            } else if ( $request['payment']['status'] == 'voided' ) {
                $payment->update([
                    'status'=> 'voided'
                ]);
            }

        } catch ( \Payload\Exceptions\NotPermitted $e ) {
            return [
                'error' => 'There is an issue with the configuration of the store'
            ];
        }

        $this->logger->debug([
            'request' => $request["payment"],
            'response' => $payment
        ]);

        return [
            'request' => $request["payment"],
            'response' => $payment
        ];
    }
}

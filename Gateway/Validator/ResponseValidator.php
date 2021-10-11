<?php

namespace Payload\PayloadMagento\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class ResponseValidator extends AbstractValidator {
    public function validate(array $validationSubject) {
        if ( array_key_exists('error', $validationSubject['response']) )
            return $this->createResult(
                    false,
                    [__($validationSubject['response']['error'])]
                );

        $request  = $validationSubject['response']['request'];
        $response = $validationSubject['response']['response'];

        foreach ($request as $key => $value) {
            if (!is_array($value) && !is_object($value) && $response->_data[$key] != $value)
                return $this->createResult(
                    false,
                    [__($key." invalid")]
                );
        }

        return $this->createResult(true, []);
    }
}

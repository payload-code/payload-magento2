<?php

namespace Payload\PayloadMagento\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;

class ResponseValidator extends AbstractValidator {
    public function validate(array $validationSubject) {
        $request  = $validationSubject['response']['request'];
        $response = $validationSubject['response']['response'];

        foreach ($request as $key => $value) {
            if ($response->_data[$key] != $value)
                return $this->createResult(
                    false,
                    [__($key." invalid")]
                );
        }

        return $this->createResult(true, []);
    }
}

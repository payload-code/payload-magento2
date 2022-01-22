define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function( Component, rendererList ) {

        rendererList.push({
            type: 'payload',
            component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
        })

        if (window.ApplePaySession
        && window.checkoutConfig.payment.payload_applepay.active == "1") {
            rendererList.push({
                type: 'payload_applepay',
                component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
            })
        }

        if (window.checkoutConfig.payment.payload_googlepay.active == "1") {
            rendererList.push({
                type: 'payload_googlepay',
                component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
            })
        }

        return Component.extend({});
    }
);

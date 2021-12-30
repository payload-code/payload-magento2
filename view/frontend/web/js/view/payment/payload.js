define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function( Component, rendererList ) {

        rendererList.push({
            type: 'payload',
            component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
        })

        if (window.ApplePaySession) {
            rendererList.push({
                type: 'payload_applepay',
                component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
            })
        }

        rendererList.push({
            type: 'payload_googlepay',
            component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
        })

        return Component.extend({});
    }
);

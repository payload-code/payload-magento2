define([
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ], function( Component, rendererList ) {

        rendererList.push({
            type: 'payload',
            component: 'Payload_PayloadMagento/js/view/payment/method-renderer/checkout-form'
        })

        return Component.extend({});
    }
);

/*browser:true*/
/*global define*/
define([
        'Magento_Checkout/js/view/payment/default'
    ], function (Component) {

        return Component.extend({
            defaults: {
                template: 'Payload_PayloadMagento/payment/checkout-form',
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_id': this.transaction_id
                    }
                }
            },

            initPayload: function() {
                Payload(window.checkoutConfig.payment.payload.payloadClientKey)

                this.pl_checkout_form = new Payload.Form({
                    form:document.getElementById('payload-checkout-form'),
                    payment:{status: 'authorized'},
                    autosubmit: false
                })

                this.pl_checkout_form.on('error', function(error) {
                    console.log(error)
                })

                this.pl_checkout_form.on('authorized', function(data) {
                    this.transaction_id = data.transaction_id
                    this.placeOrder()
                }.bind(this))
            },

            placePayloadOrder: function() {
                this.pl_checkout_form.params.payment.amount = window.checkoutConfig.quoteData.grand_total
                this.pl_checkout_form.submit()
            }
        });
    }
);

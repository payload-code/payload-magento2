/*browser:true*/
/*global define*/
define([
        'jquery',
        'loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Vault/js/view/payment/vault-enabler'
    ], function ($, loader, quote, Component, VaultEnabler) {
        return Component.extend({
            defaults: {
                template: 'Payload_PayloadMagento/payment/checkout-form',
            },

            initialize: function () {
                this._super();
                this.vaultEnabler = new VaultEnabler();
                console.log(this)
                this.vaultEnabler.setPaymentCode(this.getVaultCode());
                return this;
            },

            getData: function() {
                var data = {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_id': this.transaction_id
                    }
                }

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
                this.vaultEnabler.visitAdditionalData(data);

                return data
            },

            isVaultEnabled: function () {
                return this.vaultEnabler.isVaultEnabled();
            },

            getVaultCode: function () {
                return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
            },

            initPayload: function() {
                Payload(window.checkoutConfig.payment.payload.client_key)

                var defaults = {
                    status: 'authorized',
                }

                if ( window.checkoutConfig.payment[this.getCode()].processing_id )
                    defaults.processing_id = window.checkoutConfig.payment[this.getCode()].processing_id

                if ( window.checkoutConfig.payment[this.getCode()].customer_id )
                    defaults.customer_id = window.checkoutConfig.payment[this.getCode()].customer_id

                this.pl_checkout_form = new Payload.Form({
                    form: document.getElementById('payload-checkout-form'),
                    payment: defaults,
                    autosubmit: false
                })

                this.pl_checkout_form.on('error', function(error) {
                    $('#payload-checkout-form').next().find('[type=submit]').prop('disabled', false)

                    var error_msg = error.error_description
                    if ( error.error_type == 'InvalidAttributes' ) {
                        if ( error.details['payment_method[card][expiry]'] )
                            error_msg = 'Please check your expiration date'
                        if ( error.details['payment_method[card][card_number]'] )
                            error_msg = 'Invalid card number'
                        if ( error.details['payment_method[card][card_number]'] )
                            error_msg = 'Invalid card number'
                    }

                    this.messageContainer.addErrorMessage({message: error_msg});
                    console.log(error)
                }.bind(this))

                this.pl_checkout_form.on('authorized', function(data) {
                    this.transaction_id = data.transaction_id
                    this.placeOrder()
                    $('#payload-checkout-form').next().find('[type=submit]').prop('disabled', false)
                }.bind(this))

                this.pl_checkout_form.on('processing', function(data) {
                    $('#payload-checkout-form').next().find('[type=submit]').prop('disabled', true)
                }.bind(this))

                this.pl_checkout_form.on('declined', function(data) {
                    $('#payload-checkout-form').next().find('[type=submit]').prop('disabled', false)
                    this.messageContainer.addErrorMessage({message: data.message});
                }.bind(this))

            },

            placePayloadOrder: function() {
                this.pl_checkout_form.params.payment.amount = quote.totals().base_grand_total

                var billing_address = quote.billingAddress()
                if ( billing_address )
                    this.pl_checkout_form.params.payment.payment_method = {
                        billing_address: {
                            street_address: billing_address.street[0],
                            unit_number: billing_address.street[1],
                            city: billing_address.city,
                            state_province: billing_address.regionCode,
                            postal_code: billing_address.postcode
                        }
                    }


                this.pl_checkout_form.submit()
            }
        });
    }
);

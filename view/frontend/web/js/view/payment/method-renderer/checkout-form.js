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

            isCardsEnabled: function () {
                return window.checkoutConfig.payment[this.getCode()].cards_enabled == "1";
            },

            isACHEnabled: function () {
                return window.checkoutConfig.payment[this.getCode()].ach_enabled == "1";
            },

            isGooglePayEnabled: function () {
                return window.checkoutConfig.payment[this.getCode()].googlepay_enabled == "1";
            },

            isApplePayEnabled: function () {
                return window.checkoutConfig.payment[this.getCode()].applepay_enabled == "1";
            },

            showACHTab: function() {
                if ( this.ach_tab )
                    $(this.ach_tab).insertAfter($('#card-tab-content'))

                if ( !this.card_tab )
                    this.card_tab = $('#card-tab-content')[0]

                $('#card-tab-content').remove()
                $('#card-tab').removeClass('active')
                $('#ach-tab').addClass('active')
            },

            showCardTab: function() {
                if ( this.card_tab )
                    $(this.card_tab).insertAfter($('#ach-tab-content'))

                if ( !this.ach_tab )
                    this.ach_tab = $('#ach-tab-content')[0]

                $('#ach-tab-content').remove()
                $('#ach-tab').removeClass('active')
                $('#card-tab').addClass('active')
            },

            initPayload: function() {
                Payload(window.checkoutConfig.payment.payload.client_key)

                if ( this.isGooglePayEnabled() ) {
                    var paymentsClient = new google.payments.api.PaymentsClient({
                        environment: 'TEST'
                    });
                    var button = paymentsClient.createButton({onClick: () => {}});
                    $('.google-pay-support>div').html(button)
                }

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

                if ( this.isCardsEnabled() )
                    this.showCardTab()

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
                        if ( error.details['payment_method[bank_account][account_number]'] )
                            error_msg = 'Invalid account number'
                        if ( error.details['payment_method[bank_account][routing_number]'] )
                            error_msg = 'Invalid routing number'
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


                if ( this.isApplePayEnabled() ) {
                    this.pl_checkout_form.applepay(function(active) {
                        if (!active)
                            $('.apple-pay-button').hide()
                        else
                            $('.apple-pay-button').click(function() {
                                this.buildPaymentRequest()
                                this.pl_checkout_form.applepay('open')
                            }.bind(this))
                    }.bind(this))

                }

                if ( this.isGooglePayEnabled() ) {
                    this.pl_checkout_form.googlepay( function(active) {
                        if (!active)
                            $('.google-pay-support button').hide()
                        else
                            $('.google-pay-support button').click(function() {
                                this.buildPaymentRequest()
                                this.pl_checkout_form.googlepay('open')
                            }.bind(this))
                    }.bind(this))
                }

            },

            buildPaymentRequest: function() {
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
            },

            placePayloadOrder: function() {
                this.buildPaymentRequest()

                this.pl_checkout_form.submit()
            }
        });
    }
);

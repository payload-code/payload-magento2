/*browser:true*/
/*global define*/
define([
        'jquery',
        'loader',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Vault/js/view/payment/vault-enabler',
        'mage/validation'
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
                return this.getCode() == 'payload'
                    && window.checkoutConfig.payment[this.getCode()].cards_enabled == "1";
            },

            isACHEnabled: function () {
                return this.getCode() == 'payload'
                    && window.checkoutConfig.payment[this.getCode()].ach_enabled == "1";
            },

            isGooglePayEnabled: function () {
                return this.getCode() == 'payload_googlepay'
            },

            isApplePayEnabled: function () {
                return this.getCode() == 'payload_applepay'
            },

            isMultiShipping: function () {
                return this.item.is_multi_shipping;
            },

            showACHTab: function() {
                if ( this.ach_tab )
                    $(this.ach_tab).insertAfter($(this.el).find('.card-tab-content'))

                if ( !this.card_tab )
                    this.card_tab = $(this.el).find('.card-tab-content')[0]

                $(this.el).find('.card-tab-content').remove()
                $(this.el).find('.card-tab').removeClass('active')
                $(this.el).find('.ach-tab').addClass('active')
            },

            showCardTab: function() {
                if ( this.card_tab )
                    $(this.card_tab).insertAfter($(this.el).find('.ach-tab-content'))

                if ( !this.ach_tab )
                    this.ach_tab = $(this.el).find('.ach-tab-content')[0]

                $(this.el).find('.ach-tab-content').remove()
                $(this.el).find('.ach-tab').removeClass('active')
                $(this.el).find('.card-tab').addClass('active')
            },

            initPayload: function(el) {
                this.el = el

                Payload(window.checkoutConfig.payment.payload.client_key)
                $(this.el).mage('validation')

                if ( this.isGooglePayEnabled() ) {
                    var paymentsClient = new google.payments.api.PaymentsClient({
                        environment: 'TEST'
                    });
                    var button = paymentsClient.createButton({onClick: () => {}});
                    $(this.el).find('.google-pay-support>div').html(button)
                }

                var defaults = {
                    status: 'authorized',
                }

                if ( window.checkoutConfig.payment[this.getCode()].processing_id )
                    defaults.processing_id = window.checkoutConfig.payment[this.getCode()].processing_id

                if ( window.checkoutConfig.payment[this.getCode()].customer_id )
                    defaults.customer_id = window.checkoutConfig.payment[this.getCode()].customer_id

                this.pl_checkout_form = new Payload.Form({
                    form: this.el,
                    payment: defaults,
                    autosubmit: false
                })

                if ( this.isCardsEnabled() )
                    this.showCardTab()

                this.pl_checkout_form.on('error', function(error) {
                    $(this.el).next().find('[type=submit]').prop('disabled', false)

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

                    malert({
                        title: 'Error message',
                        content: error_msg,
                        modalClass: 'checkout-alert',
                        clickableOverlay: false,
                        focus: '[data-role="action"]',
                        actions: {
                            always: function () {
                            }
                        },
                        buttons: [{
                            text: $.mage.__('OK'),
                            class: 'action-primary action-accept',
                            click: function () {
                                this.closeModal(true);
                            }
                        }],
                        keyEventHandlers: {

                            enterKey: function (event) {
                                if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                                    this.options.isOpen && this.modal[0] === document.activeElement) {
                                    this.closeModal(event);
                                }
                            },
                            spaceKey: function (event) {
                                if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                                    this.options.isOpen && this.modal[0] === document.activeElement) {
                                    this.closeModal(event);
                                }
                            }
                        }
                    })

                    console.log(error)
                }.bind(this))

                this.pl_checkout_form.on('authorized', function(data) {
                    this.transaction_id = data.transaction_id
                    this.placeOrder()
                    $(this.el).next().find('[type=submit]').prop('disabled', false)
                }.bind(this))

                this.pl_checkout_form.on('processing', function(data) {
                    $(this.el).next().find('[type=submit]').prop('disabled', true)
                }.bind(this))

                this.pl_checkout_form.on('declined', function(data) {
                    $(this.el).next().find('[type=submit]').prop('disabled', false)
                    this.messageContainer.addErrorMessage({message: data.message});
                }.bind(this))


                if ( this.isApplePayEnabled() ) {
                    this.pl_checkout_form.applepay(function(active) {
                        if (!active) {
                            $(this.el).find('.apple-pay-button').hide()
                            $(this.el).find('.apple-pay-support+.wallet-not-available').show()
                        } else
                            $(this.el).find('.apple-pay-button').click(function() {
                                if ( this.validate() ) {
                                    this.buildPaymentRequest()
                                    this.pl_checkout_form.applepay('open')
                                }
                            }.bind(this))
                    }.bind(this))

                }

                if ( this.isGooglePayEnabled() ) {
                    this.pl_checkout_form.googlepay( function(active) {
                        if (!active) {
                            $(this.el).find('.google-pay-support button').hide()
                            $(this.el).find('.google-pay-support+.wallet-not-available').show()
                        } else
                            $(this.el).find('.google-pay-support button').click(function() {
                                if ( this.validate() ) {
                                    this.buildPaymentRequest()
                                    this.pl_checkout_form.googlepay('open')
                                }
                            }.bind(this))
                    }.bind(this))
                }

            },

            validate: function() {
                return $(this.el).validation('isValid')
            },

            buildPaymentRequest: function() {
                this.pl_checkout_form.params.payment.amount = quote.totals().base_grand_total

                var billing_address = quote.billingAddress()
                if ( billing_address )
                    this.pl_checkout_form.params.payment.payment_method = {
                        account_holder: billing_address.firstname + ' ' + billing_address.lastname,
                        email: billing_address.email || quote.guestEmail,
                        billing_address: {
                            street_address: billing_address.street[0],
                            unit_number: billing_address.street[1],
                            city: billing_address.city,
                            state_province: billing_address.regionCode,
                            postal_code: billing_address.postcode,
                            country_code: billing_address.countryId
                        }
                    }

                var shipping_address = quote.shippingAddress()
                if ( shipping_address )
                    this.pl_checkout_form.params.payment.shipping_details = {
                        rcpt_first_name: shipping_address.firstname,
                        rcpt_last_name: shipping_address.lastname,
                        rcpt_company: shipping_address.company,
                        street_address: shipping_address.street[0],
                        unit_number: shipping_address.street[1],
                        city: shipping_address.city,
                        state_province: shipping_address.regionCode,
                        postal_code: shipping_address.postcode,
                        country_code: shipping_address.countryId
                    }
            },

            placePayloadOrder: function() {
                if ( this.validate() ) {
                    this.buildPaymentRequest()
                    this.pl_checkout_form.submit()
                }
            }
        });
    }
);

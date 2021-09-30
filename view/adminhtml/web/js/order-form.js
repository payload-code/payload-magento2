define(['jquery', 'Magento_Ui/js/modal/alert'], function($, malert){
    "use strict";

    var payload_config, order, ach_tab, card_tab, initialized = false

    function showACHTab() {
        if ( ach_tab )
            $(ach_tab).insertAfter($('#card-tab-content'))

        if ( !card_tab )
            card_tab = $('#card-tab-content')[0]

        $('#card-tab-content').remove()
        $('#card-tab').removeClass('active')
        $('#ach-tab').addClass('active')
    }

    function showCardTab() {
        if ( card_tab )
            $(card_tab).insertAfter($('#ach-tab-content'))

        if ( !ach_tab )
            ach_tab = $('#ach-tab-content')[0]

        $('#ach-tab-content').remove()
        $('#ach-tab').removeClass('active')
        $('#card-tab').addClass('active')
    }

    function initPayload() {
        Payload(payload_config.client_key)
        var submit_btns = $('#submit_order_top_button, #edit_form .order-totals-actions button.primary')

        $('#ach-tab').click(showACHTab)
        $('#card-tab').click(showCardTab)

        if ( payload_config.cards_enabled == "1" )
            showCardTab()

        var defaults = {
            status: 'authorized',
        }

        if ( payload_config.processing_id )
            defaults.processing_id = payload_config.processing_id

        var pl_checkout_form = new Payload.Form({
            type: 'payment',
            form: document.getElementById('edit_form'),
            payment: defaults,
            autosubmit: false
        }).on('processing', function(data) {
            submit_btns.prop('disabled', true)
        }).on('error', function(error) {

            if ( error.error_type == "TransactionDeclined" )
                return

            submit_btns.prop('disabled', false)

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
                title: 'Invalid Payment Details',
                content: error_msg,
                clickableOverlay: false,
                actions: {
                    always: function () {}
                }
            })

            console.log(error)
        }).on('authorized', function(e) {
            submit_btns.prop('disabled', false)

            $('#edit_form input[name=pl_transaction_id]')
                .attr('name', 'payment[transaction_id]')

            order._submit()
        }).on('declined', function(data) {
            submit_btns.prop('disabled', false)

            malert({
                title: 'Declined',
                content: data.message,
                clickableOverlay: false,
                actions: {
                    always: function () {}
                }
            })
        })


        if ( !order._submit )
            order._submit = order.submit

        order.submit = function() {
            pl_checkout_form.params.payment.amount = $('#grand-total .admin__total-amount .price, #grand-total-include-tax .admin__total-amount .price').text()
            pl_checkout_form.submit()
        }

    }

    return function orderform(config) {
        if ( initialized ) return

        payload_config = config.payment.payload

        var wait = setInterval(function() {
            if ( window.order ) {
                clearInterval(wait)
                order = window.order
                initPayload()
            }
        }, 100)
    }
});

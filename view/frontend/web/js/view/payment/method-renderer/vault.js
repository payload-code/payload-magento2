define([
    'Magento_Vault/js/view/payment/method-renderer/vault',
], function (VaultComponent) {

    return VaultComponent.extend({
        defaults: {
            template: 'Payload_PayloadMagento/payment/vault'
        },

        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        getCardType: function () {
            return this.details.type;
        },

        getToken: function () {
            return this.publicHash;
        },

        getData: function () {
            var data = {
                method: this.getCode()
            };

            data['additional_data'] = {};
            data['additional_data']['public_hash'] = this.getToken();
            data['additional_data']['customer_id'] = window.checkoutConfig.customerData.id;

            return data;
        }
    });
});

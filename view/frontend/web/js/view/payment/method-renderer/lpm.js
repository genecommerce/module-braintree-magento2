define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'jquery',
        'braintree',
        'braintreeLpm',
        'Magento_Braintree/js/form-builder',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/quote',
        'mage/translate'
    ],
    function (
        Component,
        ko,
        $,
        braintree,
        lpm,
        formBuilder,
        messageList,
        selectBillingAddress,
        fullScreenLoader,
        quote,
        $t
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                code: 'braintree_local_payment',
                paymentMethodNonce: null,
                template: 'Magento_Braintree/payment/lpm',
            },

            getClientToken: function () {
                return window.checkoutConfig.payment[this.getCode()].clientToken;
            },

            getCode: function () {
                return this.code;
            },

            getData: function () {
                let data = {
                    'method': this.getCode(),
                    'additional_data': {
                        'payment_method_nonce': this.paymentMethodNonce,
                    }
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

                return data;
            },

            getTitle: function() {
                return window.checkoutConfig.payment[this.getCode()].title;
            },

            initialize: function () {
                this._super();

                var self = this;

                return this;
            },

            setErrorMsg: function (message) {
                messageList.addErrorMessage({
                    message: message
                });
            },

            setPaymentMethodNonce: function (nonce) {
                this.paymentMethodNonce = nonce;
            },

            validateForm: function (form) {
                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);

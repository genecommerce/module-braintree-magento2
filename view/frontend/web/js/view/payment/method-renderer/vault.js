/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'ko',
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Ui/js/model/messageList',
    'Magento_Braintree/js/view/payment/validator-handler',
    'Magento_Checkout/js/model/full-screen-loader'
], function (
    ko,
    $,
    VaultComponent,
    Braintree,
    globalMessageList,
    validatorManager,
    fullScreenLoader
) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Magento_Braintree/payment/cc/vault',
            modules: {
                hostedFields: '${ $.parentName }.braintree'
            },
            vaultedCVV: ko.observable(""),
            validatorManager: validatorManager,
            paymentMethodNonce: null,
            additionalData: {}
        },

        initObservable: function () {
            this._super()
                .observe(['active']);
            this.validatorManager.initialize();

            return this;
        },

        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        /**
         * Get show CVV Field
         * @returns {Boolean}
         */
        getShowCvv: function () {
            return window.checkoutConfig.payment[this.code].useCvvVault;
        },

        /**
         * Place order
         */
        placeOrder: function () {
            this.getPaymentMethodNonce();
        },

        /**
         * Send request to get payment method nonce
         */
        getPaymentMethodNonce: function () {
            var self = this;

            fullScreenLoader.startLoader();
            $.getJSON(self.nonceUrl, {
                'public_hash': self.publicHash,
                'cvv': self.vaultedCVV()
            }).done(function (response) {
                fullScreenLoader.stopLoader();
                self.hostedFields(function (formComponent) {
                    formComponent.setPaymentMethodNonce(response.paymentMethodNonce);
                    formComponent.additionalData['public_hash'] = self.publicHash;
                    formComponent.code = self.code;
                    if (self.vaultedCVV()) {
                        formComponent.additionalData['cvv'] = self.vaultedCVV();
                    }

                    self.validatorManager.validate(formComponent, function () {
                        return formComponent.placeOrder('parent');
                    }, function() {
                        // No teardown actions required.
                    });

                });
            }).fail(function (response) {
                var error = JSON.parse(response.responseText);

                fullScreenLoader.stopLoader();
                globalMessageList.addErrorMessage({
                    message: error.message
                });
            });
        }
    });
});

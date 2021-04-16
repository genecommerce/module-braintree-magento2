/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_Braintree/js/view/payment/method-renderer/hosted-fields',
    'Magento_Braintree/js/validator',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-payment-information-extended',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Braintree/js/view/payment/adapter'
], function (
    $,
    Component,
    validator,
    messageList,
    $t,
    fullScreenLoader,
    setPaymentInformationExtended,
    additionalValidators,
    braintree
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/multishipping/form'
        },

        /**
         * Get list of available CC types
         *
         * @returns {Object}
         */
        getCcAvailableTypes: function () {
            var availableTypes = validator.getAvailableCardTypes(),
                billingCountryId;

            billingCountryId = $('#multishipping_billing_country_id').val();

            if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {
                return validator.collectTypes(
                    availableTypes, validator.getCountrySpecificCardTypes(billingCountryId)
                );
            }

            return availableTypes;
        },

        /**
         * @override
         */
        handleNonce: function (data) {
            var self = this;
            this.setPaymentMethodNonce(data.nonce);

            // place order on success validation
            self.validatorManager.validate(self, function () {
                return self.setPaymentInformation();
            }, function() {
                self.isProcessing = false;
                self.paymentMethodNonce = null;
            });
        },

        /**
         * @override
         */
        placeOrder: function () {
            if (this.isProcessing) {
                return false;
            } else {
                this.isProcessing = true;
            }

            braintree.tokenizeHostedFields();
            return false;
        },

        /**
         * @override
         */
        setPaymentInformation: function () {
            if (additionalValidators.validate()) {
                fullScreenLoader.startLoader();
                $.when(
                    setPaymentInformationExtended(
                        this.messageContainer,
                        this.getData(),
                        true
                    )
                ).done(this.done.bind(this))
                    .fail(this.fail.bind(this));
            }
        },

        /**
         * {Function}
         */
        fail: function () {
            fullScreenLoader.stopLoader();

            return this;
        },

        /**
         * {Function}
         */
        done: function () {
            fullScreenLoader.stopLoader();
            $('#multishipping-billing-form').submit();

            return this;
        }
    });
});

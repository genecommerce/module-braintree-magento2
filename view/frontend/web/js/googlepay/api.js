/**
 * Braintree Google Pay button api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define([
    'uiComponent',
    'mage/translate',
    'mage/storage',
    'jquery',
    'Magento_Braintree/js/form-builder'
], function (Component, $t, storage, jQuery, formBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            clientToken: null,
            merchantId: null,
            currencyCode: null,
            actionSuccess: null,
            amount: null,
            cardTypes: []
        },

        /**
         * Set & get environment
         * "PRODUCTION" or "TEST"
         */
        setEnvironment: function (value) {
            this.environment = value;
        },
        getEnvironment: function () {
            return this.environment;
        },

        /**
         * Set & get api token
         */
        setClientToken: function (value) {
            this.clientToken = value;
        },
        getClientToken: function () {
            return this.clientToken;
        },

        /**
         * Set and get display name
         */
        setMerchantId: function (value) {
            this.merchantId = value;
        },
        getMerchantId: function () {
            return this.merchantId;
        },

        /**
         * Set and get currency code
         */
        setAmount: function (value) {
            this.amount = parseFloat(value).toFixed(2);
        },
        getAmount: function () {
            return this.amount;
        },

        /**
         * Set and get currency code
         */
        setCurrencyCode: function (value) {
            this.currencyCode = value;
        },
        getCurrencyCode: function () {
            return this.currencyCode;
        },

        /**
         * Set and get success redirection url
         */
        setActionSuccess: function (value) {
            this.actionSuccess = value;
        },
        getActionSuccess: function () {
            return this.actionSuccess;
        },

        /**
         * Set and get success redirection url
         */
        setCardTypes: function (value) {
            this.cardTypes = value;
        },
        getCardTypes: function () {
            return this.cardTypes;
        },

        /**
         * Payment request info
         */
        getPaymentRequest: function () {
            var result = {
                transactionInfo: {
                    totalPriceStatus: 'ESTIMATED',
                    totalPrice: this.getAmount(),
                    currencyCode: this.getCurrencyCode()
                },
                allowedPaymentMethods: ['CARD'],
                phoneNumberRequired: true,
                emailRequired: true,
                shippingAddressRequired: true,
                cardRequirements: {
                    billingAddressRequired: true,
                    billingAddressFormat: 'FULL',
                    allowedCardNetworks: this.getCardTypes()
                }
            };

            if (this.getEnvironment() !== "TEST") {
                result['merchantId'] = this.getMerchantId();
            }

            return result;
        },

        /**
         * Place the order
         */
        startPlaceOrder: function (nonce, paymentData) {
            var payload = {
                details: {
                    shippingAddress: {
                        streetAddress: paymentData.shippingAddress.address1 + "\n"
                        + paymentData.shippingAddress.address2,
                        locality: paymentData.shippingAddress.locality,
                        postalCode: paymentData.shippingAddress.postalCode,
                        countryCodeAlpha2: paymentData.shippingAddress.countryCode,
                        email: paymentData.email,
                        name: paymentData.shippingAddress.name,
                        telephone: typeof paymentData.shippingAddress.phoneNumber !== 'undefined' ? paymentData.shippingAddress.phoneNumber : '',
                        region: typeof paymentData.shippingAddress.administrativeArea !== 'undefined' ? paymentData.shippingAddress.administrativeArea : ''
                    },
                    billingAddress: {
                        streetAddress: paymentData.cardInfo.billingAddress.address1 + "\n"
                        + paymentData.cardInfo.billingAddress.address2,
                        locality: paymentData.cardInfo.billingAddress.locality,
                        postalCode: paymentData.cardInfo.billingAddress.postalCode,
                        countryCodeAlpha2: paymentData.cardInfo.billingAddress.countryCode,
                        email: paymentData.email,
                        name: paymentData.cardInfo.billingAddress.name,
                        telephone: typeof paymentData.cardInfo.billingAddress.phoneNumber !== 'undefined' ? paymentData.cardInfo.billingAddress.phoneNumber : '',
                        region: typeof paymentData.cardInfo.billingAddress.administrativeArea !== 'undefined' ? paymentData.cardInfo.billingAddress.administrativeArea : ''
                    }
                },
                nonce: nonce
            };

            formBuilder.build({
                action: this.getActionSuccess(),
                fields: {
                    result: JSON.stringify(payload)
                }
            }).submit();
        }
    });
});
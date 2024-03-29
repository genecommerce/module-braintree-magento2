/**
 * Braintree Google Pay payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Braintree/js/googlepay/button',
    'Magento_Checkout/js/model/payment/additional-validators'
], function (
    Component,
    quote,
    button,
    additionalValidators
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/googlepay/core-checkout',
            paymentMethodNonce: null,
            deviceData: null,
            grandTotalAmount: 0
        },

        /**
         * Reveal additionalValidators to button.js component
         */
        getAdditionalValidators: function() {
            return additionalValidators;
        },

        /**
         * Inject the Google Pay button into the target element
         */
        getGooglePayBtn: function (id) {
            button.init(
                document.getElementById(id),
                this
            );
        },

        /**
         * Subscribe to grand totals
         */
        initObservable: function () {
            this._super();
            this.grandTotalAmount = parseFloat(quote.totals()['base_grand_total']).toFixed(2);
            this.currencyCode = quote.totals()['base_currency_code'];

            quote.totals.subscribe(function () {
                if (this.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    this.grandTotalAmount = parseFloat(quote.totals()['base_grand_total']).toFixed(2);
                }
            }.bind(this));

            return this;
        },

        /**
         * Google Pay place order method
         *
         * @param nonce
         * @param paymentData
         * @param device_data
         */
        startPlaceOrder: function (nonce, paymentData, device_data) {
            this.setPaymentMethodNonce(nonce);
            this.setDeviceData(device_data);
            this.placeOrder();
        },

        /**
         * Save nonce
         *
         * @param nonce
         */
        setPaymentMethodNonce: function (nonce) {
            this.paymentMethodNonce = nonce;
        },

        /**
         * Save device_data
         *
         * @param device_data
         */
        setDeviceData: function (device_data) {
            this.deviceData = device_data;
        },

        /**
         * Retrieve the client token
         *
         * @returns null|string
         */
        getClientToken: function () {
            return window.checkoutConfig.payment[this.getCode()].clientToken;
        },

        /**
         * Payment request info
         */
        getPaymentRequest: function () {
            var result = {
                transactionInfo: {
                    totalPriceStatus: 'FINAL',
                    totalPrice: this.grandTotalAmount,
                    currencyCode: this.currencyCode
                },
                allowedPaymentMethods: [{
                    "type": "CARD",
                    "parameters": {
                        "allowedCardNetworks": this.getCardTypes(),
                        "billingAddressRequired": false,
                    },
                }],
                shippingAddressRequired: false,
                emailRequired: false,
            };

            if (this.getEnvironment() !== "TEST") {
                result.merchantInfo = {
                    merchantId: this.getMerchantId()
                };
            }

            return result;
        },

        /**
         * Merchant display name
         */
        getMerchantId: function () {
            return window.checkoutConfig.payment[this.getCode()].merchantId;
        },

        /**
         * Environment
         */
        getEnvironment: function () {
            return window.checkoutConfig.payment[this.getCode()].environment;
        },

        /**
         * Card Types
         */
        getCardTypes: function () {
            return window.checkoutConfig.payment[this.getCode()].cardTypes;
        },

        /**
         * BTN Color
         */
        getBtnColor: function () {
            return window.checkoutConfig.payment[this.getCode()].btnColor;
        },

        /**
         * Get data
         *
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce,
                    'device_data': this.deviceData
                }
            };
        },

        /**
         * Return image url for the Google Pay mark
         */
        getPaymentMarkSrc: function () {
            return window.checkoutConfig.payment[this.getCode()].paymentMarkSrc;
        }
    });
});

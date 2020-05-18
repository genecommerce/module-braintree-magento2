/**
 * Braintree Google Pay payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Braintree/js/googlepay/button'
], function (
    Component,
    quote,
    button
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/googlepay/core-checkout',
            paymentMethodNonce: null,
            deviceSupported: button.deviceSupported(),
            grandTotalAmount: 0
        },

        /**
         * Inject the google pay button into the target element
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
         * Google pay place order method
         */
        startPlaceOrder: function (nonce, paymentData) {
            this.setPaymentMethodNonce(nonce);
            this.placeOrder();
        },

        /**
         * Save nonce
         */
        setPaymentMethodNonce: function (nonce) {
            this.paymentMethodNonce = nonce;
        },

        /**
         * Retrieve the client token
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
                allowedPaymentMethods: ['CARD'],
                phoneNumberRequired: false,
                emailRequired: false,
                shippingAddressRequired: false,
                cardRequirements: {
                    billingAddressRequired: false,
                    allowedCardNetworks: this.getCardTypes()
                }
            };

           if (this.getEnvironment() !== "TEST") {
               result['merchantId'] = this.getMerchantId();
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
         * @returns {Object}
         */
        getData: function () {
            return {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };
        },

        /**
         * Return image url for the google pay mark
         */
        getPaymentMarkSrc: function () {
            return window.checkoutConfig.payment[this.getCode()].paymentMarkSrc;
        }
    });
});
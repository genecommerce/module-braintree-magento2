/**
 * Braintree Apple Pay payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Braintree/js/applepay/button',
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
            template: 'Magento_Braintree/applepay/core-checkout',
            paymentMethodNonce: null,
            grandTotalAmount: 0,
            deviceSupported: button.deviceSupported()
        },

        /**
         * Reveal additionalValidators to button.js component
         */
        getAdditionalValidators: function() {
            return additionalValidators;
        },

        /**
         * Inject the apple pay button into the target element
         */
        getApplePayBtn: function (id) {
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

            quote.totals.subscribe(function () {
                if (this.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    this.grandTotalAmount = parseFloat(quote.totals()['base_grand_total']).toFixed(2);
                }
            }.bind(this));

            return this;
        },

        /**
         * Apple Pay place order method
         */
        startPlaceOrder: function (nonce, event, session) {
            this.setPaymentMethodNonce(nonce);
            this.placeOrder();

            session.completePayment(ApplePaySession.STATUS_SUCCESS);
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
         * Payment request data
         */
        getPaymentRequest: function () {
            return {
                total: {
                    label: this.getDisplayName(),
                    amount: this.grandTotalAmount
                }
            };
        },

        /**
         * Merchant display name
         */
        getDisplayName: function () {
            return window.checkoutConfig.payment[this.getCode()].merchantName;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            let data = {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };
            return data;
        },

        /**
         * Return image url for the Apple Pay mark
         */
        getPaymentMarkSrc: function () {
            return window.checkoutConfig.payment[this.getCode()].paymentMarkSrc;
        }
    });
});

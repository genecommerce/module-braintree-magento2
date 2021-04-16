/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'braintreeCheckoutPayPalAdapter',
    'Magento_Checkout/js/model/quote',
    'Magento_Braintree/js/view/payment/method-renderer/paypal',
    'Magento_Checkout/js/action/set-payment-information',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate'
], function (
    $,
    _,
    Braintree,
    quote,
    Component,
    setPaymentInformationAction,
    additionalValidators,
    fullScreenLoader,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/multishipping/paypal',
            submitButtonSelector: '[id="parent-payment-continue"]',
            reviewButtonHtml: ''
        },

        /**
         * @override
         */
        initObservable: function () {
            this.reviewButtonHtml = $(this.submitButtonSelector).html();
            return this._super();
        },

        initClientConfig: function () {
            this.clientConfig = _.extend(this.clientConfig, this.getPayPalConfig());
            this.clientConfig.paypal.enableShippingAddress = false;

            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);
            this.clientConfig.buttonPayPalId = 'parent-payment-continue';

        },

        /**
         * @override
         */
        onActiveChange: function (isActive) {
            this.updateSubmitButtonHtml(isActive);
            this._super(isActive);
        },

        /**
         * @override
         */
        beforePlaceOrder: function (data) {
            this._super(data);
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            this.disableButton();
            this.clientConfig.paypal.amount = parseFloat(this.grandTotalAmount).toFixed(2);

            if (!quote.isVirtual()) {
                this.clientConfig.paypal.enableShippingAddress = false;
                this.clientConfig.paypal.shippingAddressEditable = false;
            }

            Braintree.setConfig(this.clientConfig);

            if (Braintree.getPayPalInstance()) {
                Braintree.getPayPalInstance().teardown(function () {
                    Braintree.setup();
                }.bind(this));
                Braintree.setPayPalInstance(null);
            } else {
                Braintree.setup();
                this.enableButton();
            }
        },

        /**
         * Get configuration for PayPal
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var totals = quote.totals(),
                config = {};

            config.paypal = {
                flow: 'checkout',
                amount: parseFloat(this.grandTotalAmount).toFixed(2),
                currency: totals['base_currency_code'],
                locale: this.getLocale(),
                requestBillingAgreement: true,

                /**
                 * Triggers on any Braintree error
                 */
                onError: function () {
                    this.paymentMethodNonce = null;
                },

                /**
                 * Triggers if browser doesn't support PayPal Checkout
                 */
                onUnsupported: function () {
                    this.paymentMethodNonce = null;
                }
            };

            if (!quote.isVirtual()) {
                config.paypal.enableShippingAddress = false;
                config.paypal.shippingAddressEditable = false;
            }

            if (this.getMerchantName()) {
                config.paypal.displayName = this.getMerchantName();
            }

            return config;
        },

        /**
         * @override
         */
        getData: function () {
            var data = this._super();

            data['additional_data']['is_active_payment_token_enabler'] = true;

            return data;
        },

        /**
         * @override
         */
        isActiveVault: function () {
            return true;
        },

        /**
         * Skipping order review step on checkout with multiple addresses is not allowed.
         *
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return false;
        },

        /**
         * Checks if payment method nonce is already received.
         *
         * @returns {Boolean}
         */
        isPaymentMethodNonceReceived: function () {
            return this.paymentMethodNonce !== null;
        },

        /**
         * Update submit button on multi-addresses checkout billing form.
         *
         * @param {Boolean} isActive
         */
        updateSubmitButtonHtml: function (isActive) {
            if (this.isPaymentMethodNonceReceived() || !isActive) {
                $(this.submitButtonSelector).html(this.reviewButtonHtml);
            }
        },

        /**
         * @override
         */
        placeOrder: function () {
            if (!this.isPaymentMethodNonceReceived()) {
                this.payWithPayPal();
            } else {
                fullScreenLoader.startLoader();

                $.when(
                    setPaymentInformationAction(
                        this.messageContainer,
                        this.getData()
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

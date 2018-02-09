/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Braintree/js/view/payment/adapter',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/create-billing-address'
], function (
    $,
    _,
    Component,
    Braintree,
    quote,
    fullScreenLoader,
    additionalValidators,
    VaultEnabler,
    createBillingAddress
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Braintree/payment/paypal',
            code: 'braintree_paypal',
            active: false,
            paypalInstance: null,
            paymentMethodNonce: null,
            grandTotalAmount: null,
            isReviewRequired: false,
            customerEmail: null,

            /**
             * Additional payment data
             *
             * {Object}
             */
            additionalData: {},

            /**
             * PayPal client configuration
             * {Object}
             */
            clientConfig: {
                offerCredit: false,
                dataCollector: {
                    paypal: true
                },

                buttonId: 'braintree_paypal_placeholder',

                onDeviceDataRecieved: function (deviceData) {
                    this.additionalData['device_data'] = deviceData;
                },

                /**
                 * Triggers when widget is loaded
                 * @param {Object} context
                 */
                onReady: function (context) {
                    context.setupPaypal();
                },

                /**
                 * Triggers on payment nonce receive
                 * @param {Object} response
                 */
                onPaymentMethodReceived: function (response) {
                    this.beforePlaceOrder(response);
                }
            },
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            this._super()
                .observe(['active', 'isReviewRequired', 'customerEmail']);

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
            this.vaultEnabler.isActivePaymentTokenEnabler.subscribe(function () {
                self.onVaultPaymentTokenEnablerChange();
            });

            this.grandTotalAmount = quote.totals()['base_grand_total'];

            quote.totals.subscribe(function () {
                if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    self.grandTotalAmount = quote.totals()['base_grand_total'];
                }
            });

            // for each component initialization need update property
            this.isReviewRequired(false);
            this.initClientConfig();

            return this;
        },

        /**
         * Get payment name
         *
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        },

        /**
         * Get payment title
         *
         * @returns {String}
         */
        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()].title;
        },

        /**
         * Check if payment is active
         *
         * @returns {Boolean}
         */
        isActive: function () {
            var active = this.getCode() === this.isChecked();

            this.active(active);

            return active;
        },

        /**
         * Triggers when payment method change
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                return;
            }

            // need always re-init Braintree with PayPal configuration
            this.reInitPayPal();
        },

        /**
         * Init config
         */
        initClientConfig: function () {
            this.clientConfig = _.extend(this.clientConfig, this.getPayPalConfig());

            _.each(this.clientConfig, function (fn, name) {
                if (typeof fn === 'function') {
                    this.clientConfig[name] = fn.bind(this);
                }
            }, this);
        },

        /**
         * Set payment nonce
         * @param {String} paymentMethodNonce
         */
        setPaymentMethodNonce: function (paymentMethodNonce) {
            this.paymentMethodNonce = paymentMethodNonce;
        },

        /**
         * Update quote billing address
         * @param {Object}customer
         * @param {Object}address
         */
        setBillingAddress: function (customer, address) {
            var billingAddress = {
                street: [address.line1],
                city: address.city,
                postcode: address.postalCode,
                countryId: address.countryCode,
                email: customer.email,
                firstname: customer.firstName,
                lastname: customer.lastName,
                telephone: typeof customer.phone !== 'undefined' ? customer.phone : '00000000000'
            };

            billingAddress['region_code'] = typeof address.state === 'string' ? address.state : '';
            billingAddress = createBillingAddress(billingAddress);
            quote.billingAddress(billingAddress);
        },

        /**
         * Prepare data to place order
         * @param {Object} data
         */
        beforePlaceOrder: function (data) {
            this.setPaymentMethodNonce(data.nonce);

            var billingAddr = quote.billingAddress();
            if (billingAddr === null || typeof billingAddr.email === 'undefined') {
                if (typeof data.details.billingAddress !== 'undefined') {
                    this.setBillingAddress(data.details, data.details.billingAddress);
                } else if (typeof data.details.shippingAddress !== 'undefined') {
                    this.setBillingAddress(data.details, data.details.shippingAddress)
                }
            }

            this.customerEmail(data.details.email);
            this.placeOrder();
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            if (Braintree.checkout) {
                Braintree.checkout.teardown(function () {
                    Braintree.checkout = null;
                });
            }

            this.disableButton();
            this.clientConfig.paypal.amount = this.grandTotalAmount;

            Braintree.setConfig(this.clientConfig);
            Braintree.setup();
        },

        /**
         * Get locale
         * @returns {String}
         */
        getLocale: function () {
            return window.checkoutConfig.payment[this.getCode()].locale;
        },

        /**
         * Is shipping address can be editable on PayPal side
         * @returns {Boolean}
         */
        isAllowOverrideShippingAddress: function () {
            return window.checkoutConfig.payment[this.getCode()].isAllowShippingAddressOverride;
        },

        /**
         * Get configuration for PayPal
         * @returns {Object}
         */
        getPayPalConfig: function () {
            var totals = quote.totals(),
                config = {},
                isActiveVaultEnabler = this.isActiveVault();

            config.paypal = {
                flow: isActiveVaultEnabler ? 'vault' : 'checkout',
                amount: this.grandTotalAmount,
                currency: totals['base_currency_code'],
                locale: this.getLocale(),
                enableShippingAddress: true,
                shippingAddressEditable: this.isAllowOverrideShippingAddress(),
                shippingAddressOverride: this.getShippingAddress(),

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

            if (this.getMerchantName()) {
                config.paypal.displayName = this.getMerchantName();
            }

            return config;
        },

        /**
         * Get shipping address
         * @returns {Object}
         */
        getShippingAddress: function () {
            var address = quote.shippingAddress();

            if (address.postcode === null) {
                return {};
            }

            return {
                recipientName: address.firstname + ' ' + address.lastname,
                line1: address.street[0],
                city: address.city,
                countryCode: address.countryId,
                postalCode: address.postcode,
                state: address.regionCode,
                phone: address.telephone
            };
        },

        /**
         * Get merchant name
         * @returns {String}
         */
        getMerchantName: function () {
            return window.checkoutConfig.payment[this.getCode()].merchantName;
        },

        /**
         * Get data
         * @returns {Object}
         */
        getData: function () {
            var data = {
                'method': this.getCode(),
                'additional_data': {
                    'payment_method_nonce': this.paymentMethodNonce
                }
            };

            data['additional_data'] = _.extend(data['additional_data'], this.additionalData);

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * Returns payment acceptance mark image path
         * @returns {String}
         */
        getPaymentAcceptanceMarkSrc: function () {

            return window.checkoutConfig.payment[this.getCode()].paymentAcceptanceMarkSrc;
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].vaultCode;
        },

        /**
         * Check if need to skip order review
         * @returns {Boolean}
         */
        isSkipOrderReview: function () {
            return window.checkoutConfig.payment[this.getCode()].skipOrderReview;
        },

        /**
         * Checks if vault is active
         * @returns {Boolean}
         */
        isActiveVault: function () {
            return this.vaultEnabler.isVaultEnabled() && this.vaultEnabler.isActivePaymentTokenEnabler();
        },

        /**
         * Re-init PayPal Auth flow to use Vault
         */
        onVaultPaymentTokenEnablerChange: function () {
            this.clientConfig.paypal.singleUse = !this.isActiveVault();
            this.reInitPayPal();
        },

        /**
         * Disable submit button
         */
        disableButton: function () {
            // stop any previous shown loaders
            fullScreenLoader.stopLoader(true);
            fullScreenLoader.startLoader();
            $('[data-button="place"]').attr('disabled', 'disabled');
        },

        /**
         * Enable submit button
         */
        enableButton: function () {
            $('[data-button="place"]').removeAttr('disabled');
            fullScreenLoader.stopLoader();
        },

        /**
         * Triggers when customer click "Continue to PayPal" button
         */
        payWithPayPal: function () {
            if (additionalValidators.validate()) {
                Braintree.checkout.paypal.initAuthFlow();
            }
        },

        /**
         * Get button id
         * @returns {String}
         */
        getButtonId: function () {
            return this.clientConfig.buttonId;
        }
    });
});

/**
 * Copyright 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'Magento_Braintree/js/view/payment/adapter',
        'mage/translate',
        'Magento_Braintree/js/validator',
        'Magento_Braintree/js/view/payment/validator-handler',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (
        _,
        $,
        Component,
        quote,
        braintree,
        $t,
        validator,
        validatorManager,
        fullScreenLoader
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                active: false,
                braintreeClient: null,
                braintreeDeviceData: null,
                paymentMethodNonce: null,
                lastBillingAddress: null,
                validatorManager: validatorManager,
                code: 'braintree',
                isProcessing: false,

                /**
                 * Additional payment data
                 *
                 * {Object}
                 */
                additionalData: {},

                /**
                 * Braintree client configuration
                 *
                 * {Object}
                 */
                clientConfig: {
                    dataCollector: {
                        kount: true
                    },

                    onReady: function (context) {
                        context.setupHostedFields();
                    },

                    /**
                     * Triggers on payment nonce receive
                     * @param {Object} response
                     */
                    onPaymentMethodReceived: function (response) {
                        this.handleNonce(response);
                    },

                    /**
                     * Allow a new nonce to be generated
                     */
                    onPaymentMethodError: function() {
                        this.isProcessing = false;
                    },

                    /**
                     * Device data initialization
                     * @param {String} deviceData
                     */
                    onDeviceDataRecieved: function (deviceData) {
                        this.additionalData['device_data'] = deviceData;
                    },

                    /**
                     * After Braintree instance initialization
                     */
                    onInstanceReady: function () {},

                    /**
                     * Triggers on any Braintree error
                     * @param {Object} response
                     */
                    onError: function (response) {
                        this.isProcessing = false;
                        braintree.showError($t('Payment ' + this.getTitle() + ' can\'t be initialized'));
                        throw response.message;
                    },

                    /**
                     * Triggers when customer click "Cancel"
                     */
                    onCancelled: function () {
                        this.paymentMethodNonce = null;
                        this.isProcessing = false;
                    }
                },
                imports: {
                    onActiveChange: 'active'
                }
            },

            /**
             * Set list of observable attributes
             *
             * @returns {exports.initObservable}
             */
            initObservable: function () {
                validator.setConfig(window.checkoutConfig.payment[this.getCode()]);
                this._super()
                    .observe(['active']);
                this.validatorManager.initialize();
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

                this.initBraintree();
            },

            /**
             * Init config
             */
            initClientConfig: function () {
                // Advanced fraud tools settings
                if (this.hasFraudProtection()) {
                    this.clientConfig = _.extend(this.clientConfig, this.kountConfig());
                }

                _.each(this.clientConfig, function (fn, name) {
                    if (typeof fn === 'function') {
                        this.clientConfig[name] = fn.bind(this);
                    }
                }, this);
            },

            /**
             * Init Braintree configuration
             */
            initBraintree: function () {
                var intervalId = setInterval(function () {
                    // stop loader when frame will be loaded
                    if ($('#braintree-hosted-field-number').length) {
                        clearInterval(intervalId);
                        fullScreenLoader.stopLoader();
                    }
                }, 500);

                if (braintree.checkout) {
                    braintree.checkout.teardown(function () {
                        braintree.checkout = null;
                    });
                }

                fullScreenLoader.startLoader();
                braintree.setConfig(this.clientConfig);
                braintree.setup();
            },

            /**
             * @returns {Object}
             */
            kountConfig: function () {
                var config = {
                    dataCollector: {
                        kount: {
                            environment: this.getEnvironment()
                        }
                    },

                    /**
                     * Device data initialization
                     *
                     * @param {Object} checkout
                     */
                    onReady: function (context) {
                        this.additionalData['device_data'] = context.deviceData;
                        context.setupHostedFields();
                    }
                };

                if (this.getKountMerchantId()) {
                    config.dataCollector.kount.merchantId = this.getKountMerchantId();
                }

                return config;
            },

            /**
             * Get full selector name
             *
             * @param {String} field
             * @returns {String}
             */
            getSelector: function (field) {
                return '#' + this.getCode() + '_' + field;
            },

            /**
             * Get list of available CC types
             *
             * @returns {Object}
             */
            getCcAvailableTypes: function () {
                var availableTypes = validator.getAvailableCardTypes(),
                    billingAddress = quote.billingAddress(),
                    billingCountryId;

                this.lastBillingAddress = quote.shippingAddress();

                if (!billingAddress) {
                    billingAddress = this.lastBillingAddress;
                }

                billingCountryId = billingAddress.countryId;

                if (billingCountryId && validator.getCountrySpecificCardTypes(billingCountryId)) {
                    return validator.collectTypes(
                        availableTypes,
                        validator.getCountrySpecificCardTypes(billingCountryId)
                    );
                }

                return availableTypes;
            },

            /**
             * @returns {Boolean}
             */
            hasFraudProtection: function () {
                return window.checkoutConfig.payment[this.getCode()].hasFraudProtection;
            },

            /**
             * @returns {String}
             */
            getEnvironment: function () {
                return window.checkoutConfig.payment[this.getCode()].environment;
            },

            /**
             * @returns {String}
             */
            getKountMerchantId: function () {
                return window.checkoutConfig.payment[this.getCode()].kountMerchantId;
            },

            /**
             * Get data
             *
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

                return data;
            },

            /**
             * Set payment nonce
             * @param {String} paymentMethodNonce
             */
            setPaymentMethodNonce: function (paymentMethodNonce) {
                this.paymentMethodNonce = paymentMethodNonce;
            },

            /**
             * Prepare data to place order
             * @param {Object} data
             */
            handleNonce: function (data) {
                var self = this;

                this.setPaymentMethodNonce(data.nonce);

                // place order on success validation
                self.validatorManager.validate(self, function () {
                    return self.placeOrder('parent');
                }, function() {
                    self.isProcessing = false;
                    self.paymentMethodNonce = null;
                });
            },

            /**
             * Action to place order
             * @param {String} key
             */
            placeOrder: function (key) {
                if (key) {
                    return this._super();
                }

                if (this.isProcessing) {
                    return false;
                } else {
                    this.isProcessing = true;
                }

                braintree.tokenizeHostedFields();
                return false;
            }
        });
    }
);

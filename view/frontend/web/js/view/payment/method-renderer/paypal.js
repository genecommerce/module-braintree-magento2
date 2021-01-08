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
    'braintree',
    'braintreeCheckoutPayPalAdapter',
    'braintreePayPalCheckout',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/create-billing-address',
    'Magento_Checkout/js/action/select-billing-address',
    'mage/translate'
], function (
    $,
    _,
    Component,
    braintree,
    Braintree,
    paypalCheckout,
    quote,
    fullScreenLoader,
    additionalValidators,
    stepNavigator,
    VaultEnabler,
    createBillingAddress,
    selectBillingAddress,
    $t
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
                offerCreditOnly: false,
                dataCollector: {
                    paypal: true
                },

                buttonPayPalId: 'braintree_paypal_placeholder',
                buttonCreditId: 'braintree_paypal_credit_placeholder',
                buttonPaylaterId: 'braintree_paypal_paylater_placeholder',

                onDeviceDataRecieved: function (deviceData) {
                    this.additionalData['device_data'] = deviceData;
                },

                /**
                 * Triggers when widget is loaded
                 * @param {Object} context
                 */
                onReady: function (context) {
                    this.setupPayPal();
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

            window.addEventListener('hashchange', function (e) {
                var methodCode = quote.paymentMethod();

                if (methodCode === 'braintree_paypal' || methodCode === 'braintree_paypal_vault') {
                    if (e.newURL.indexOf('payment') > 0 && self.grandTotalAmount !== null) {
                        self.reInitPayPal();
                    }
                }
            });

            quote.paymentMethod.subscribe(function (value) {
                var methodCode = value;

                if (methodCode === 'braintree_paypal' || methodCode === 'braintree_paypal_vault') {
                    self.reInitPayPal();
                }
            });

            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
            this.vaultEnabler.isActivePaymentTokenEnabler.subscribe(function () {
                self.onVaultPaymentTokenEnablerChange();
            });

            this.grandTotalAmount = quote.totals()['base_grand_total'];

            quote.totals.subscribe(function () {
                if (self.grandTotalAmount !== quote.totals()['base_grand_total']) {
                    self.grandTotalAmount = quote.totals()['base_grand_total'];
                    var methodCode = quote.paymentMethod();

                    if (methodCode === 'braintree_paypal' || methodCode === 'braintree_paypal_vault') {
                        self.reInitPayPal();
                    }
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
            this.customerEmail(data.details.email);
            if (quote.isVirtual()) {
                this.isReviewRequired(true);
            } else {
                if (this.isRequiredBillingAddress() === '1' || quote.billingAddress() === null) {
                    if (typeof data.details.billingAddress !== 'undefined') {
                        this.setBillingAddress(data.details, data.details.billingAddress);
                    } else {
                        this.setBillingAddress(data.details, data.details.shippingAddress);
                    }
                } else {
                    if (quote.shippingAddress() === quote.billingAddress()) {
                        selectBillingAddress(quote.shippingAddress());
                    } else {
                        selectBillingAddress(quote.billingAddress());
                    }
                }

                this.placeOrder();
            }
        },

        /**
         * Re-init PayPal Auth Flow
         */
        reInitPayPal: function () {
            this.disableButton();
            this.clientConfig.paypal.amount = parseFloat(this.grandTotalAmount).toFixed(2);

            if (!quote.isVirtual()) {
                this.clientConfig.paypal.enableShippingAddress = true;
                this.clientConfig.paypal.shippingAddressEditable = false;
                this.clientConfig.paypal.shippingAddressOverride = this.getShippingAddress();
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
         * Setup PayPal instance
         */
        setupPayPal: function () {
            var self = this;

            if (Braintree.config.paypalInstance) {
                fullScreenLoader.stopLoader(true);
                return;
            }

            paypalCheckout.create({
                client: Braintree.clientInstance
            }, function (createErr, paypalCheckoutInstance) {
                if (createErr) {
                    Braintree.showError($t("PayPal Checkout could not be initialized. Please contact the store owner."));
                    console.error('paypalCheckout error', createErr);
                    return;
                }
                let quoteObj = quote.totals();
                paypalCheckoutInstance.loadPayPalSDK({
                    components: 'buttons,messages,funding-eligibility',
                    currency: quoteObj['base_currency_code'],
                }, function () {
                    this.loadPayPalButton(paypalCheckoutInstance, 'paypal');
                    if(this.isCreditEnabled()) {
                        this.loadPayPalButton(paypalCheckoutInstance, 'credit');
                    }
                    if(this.isPaylaterEnabled()) {
                        this.loadPayPalButton(paypalCheckoutInstance, 'paylater');
                    }

                }.bind(this));
            }.bind(this));
        },

        loadPayPalButton: function (paypalCheckoutInstance, funding) {
            var paypalPayment = Braintree.config.paypal,
                onPaymentMethodReceived = Braintree.config.onPaymentMethodReceived,
                style = {
                    color: Braintree.getColor(),
                    shape: Braintree.getShape(),
                    layout: Braintree.getLayout(),
                    size: Braintree.getSize()
                };

            if (Braintree.getBranding()) {
                style.branding = Braintree.getBranding();
            }
            if (Braintree.getFundingIcons()) {
                style.fundingicons = Braintree.getFundingIcons();
            }

            if (funding === 'credit') {
                style.layout = "horizontal";
                style.color = "darkblue";
                Braintree.config.buttonId = this.clientConfig.buttonCreditId;
            } else if (funding === 'paylater') {
                style.layout = "horizontal";
                style.color = "white";
                Braintree.config.buttonId = this.clientConfig.buttonPaylaterId;
            } else {
                Braintree.config.buttonId = this.clientConfig.buttonPayPalId;
            }
            // Render
            Braintree.config.paypalInstance = paypalCheckoutInstance;
            var events = Braintree.events;
            $('#' + Braintree.config.buttonId).html('');

            var button = paypal.Buttons({
                fundingSource: funding,
                env: Braintree.getEnvironment(),
                style: style,
                commit: true,
                locale: Braintree.config.paypal.locale,

                createOrder: function () {
                    return paypalCheckoutInstance.createPayment(paypalPayment);
                },

                onCancel: function (data) {
                    console.log('checkout.js payment cancelled', JSON.stringify(data, 0, 2));

                    if (typeof events.onCancel === 'function') {
                        events.onCancel();
                    }
                },

                onError: function (err) {
                    Braintree.showError($t("PayPal Checkout could not be initialized. Please contact the store owner."));
                    Braintree.config.paypalInstance = null;
                    console.error('Paypal checkout.js error', err);

                    if (typeof events.onError === 'function') {
                        events.onError(err);
                    }
                }.bind(this),

                onClick: function(data) {
                    if (typeof events.onClick === 'function') {
                        events.onClick(data);
                    }
                },

                onApprove: function (data, actions) {
                    return paypalCheckoutInstance.tokenizePayment(data)
                        .then(function (payload) {
                            onPaymentMethodReceived(payload);
                        });
                }

            });
            if (button.isEligible()) {
                button.render('#' + Braintree.config.buttonId).then(function () {
                    Braintree.enableButton();
                    if (typeof Braintree.config.onPaymentMethodError === 'function') {
                        Braintree.config.onPaymentMethodError();
                    }
                }.bind(this)).then(function (data) {
                    if (typeof events.onRender === 'function') {
                        events.onRender(data);
                    }
                });
            }
        },

        /**
         * Get locale
         * @returns {String}
         */
        getLocale: function () {
            return window.checkoutConfig.payment[this.getCode()].locale;
        },

        /**
         * Is Billing Address required from PayPal side
         * @returns {exports.isRequiredBillingAddress|(function())|boolean}
         */
        isRequiredBillingAddress: function () {
            return window.checkoutConfig.payment[this.getCode()].isRequiredBillingAddress;
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
                flow: 'checkout',
                amount: parseFloat(this.grandTotalAmount).toFixed(2),
                currency: totals['base_currency_code'],
                locale: this.getLocale(),

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

            if (isActiveVaultEnabler) {
                config.paypal.requestBillingAgreement = true;
            }

            if (!quote.isVirtual()) {
                config.paypal.enableShippingAddress = true;
                config.paypal.shippingAddressEditable = false;
                config.paypal.shippingAddressOverride = this.getShippingAddress();
            }

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

            return {
                recipientName: address.firstname + ' ' + address.lastname,
                line1: address.street[0],
                line2: typeof address.street[2] === 'undefined' ? address.street[1] : address.street[1] + ' ' + address.street[2],
                city: address.city,
                countryCode: address.countryId,
                postalCode: address.postcode,
                state: address.region
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
            fullScreenLoader.stopLoader(true);
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
        getPayPalButtonId: function () {
            return this.clientConfig.buttonPayPalId;
        },

        /**
         * Get button id
         * @returns {String}
         */
        getCreditButtonId: function () {
            return this.clientConfig.buttonCreditId;
        },

        /**
         * Get button id
         * @returns {String}
         */
        getPaylaterButtonId: function () {
            return this.clientConfig.buttonPaylaterId;
        },

        isPaylaterEnabled: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['isActive'];
        },

        isPaylaterMessageEnabled: function () {
            return window.checkoutConfig.payment['braintree_paypal_paylater']['isMessageActive'];
        },

        getGrandTotalAmount: function() {
            return parseFloat(this.grandTotalAmount).toFixed(2);
        },

        isCreditEnabled: function () {
            return window.checkoutConfig.payment['braintree_paypal_credit']['isActive'];
        },

    });
});

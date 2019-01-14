/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'rjsResolver',
        'uiRegistry',
        'uiComponent',
        'underscore',
        'jquery',
        'braintree',
        'braintreeDataCollector',
        'braintreePayPalCheckout',
        'Magento_Braintree/js/form-builder',
        'domReady!',
        'https://www.paypalobjects.com/api/checkout.js'
    ],
    function (
        resolver,
        registry,
        Component,
        _,
        $,
        braintree,
        dataCollector,
        paypalCheckout,
        formBuilder
    ) {
        'use strict';

        return Component.extend({

            defaults: {

                integrationName: 'braintreePaypal.currentIntegration',

                /**
                 * {String}
                 */
                displayName: null,

                /**
                 * {String}
                 */
                environment: 'sandbox',

                /**
                 * {String}
                 */
                clientToken: null,

                /**
                 * {String}
                 */
                payeeEmail: null,

                /**
                 * {String}
                 */
                color: null,

                /**
                 * {String}
                 */
                shape: null,

                /**
                 * {String}
                 */
                size: null,

                /**
                 * {String}
                 */
                layout: null,

                /**
                 * {String}
                 */
                offerCredit: false,

                /**
                 * {Object}
                 */
                disabledFunding: {
                    card: false,
                    elv: false
                }
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super()
                    .initComponent();

                return this;
            },

            /**
             * @returns {Object}
             */
            initComponent: function () {
                var $this = $('#' + this.id),
                    data = {
                        amount: $this.data('amount'),
                        locale: $this.data('locale'),
                        currency: $this.data('currency'),
                        flow: 'checkout',
                        enableShippingAddress: true,
                        payee: {
                            email: this.payeeEmail
                        },
                        displayName: this.displayName,
                        offerCredit: this.offerCredit
                    };

                this.initCallback(data);
                return this;
            },

            initCallback: function (data) {
                braintree.create({
                    authorization: this.clientToken,
                }, function (clientErr, clientInstance) {
                    if (clientErr) {
                        console.error('paypalCheckout error', clientErr);
                        return this.showError("PayPal Checkout could not be initialized. Please contact the store owner.");
                    }

                    dataCollector.create({
                        client: clientInstance,
                        paypal: true
                    }, function (err, dataCollectorInstance) {
                        if (err) {
                            return console.log(err);
                        }
                    });

                    paypalCheckout.create({
                        client: clientInstance
                    }, function (createErr, paypalCheckoutInstance) {
                        if (createErr) {
                            console.error('paypalCheckout instantiation error', createErr);
                            return;
                        }

                        var style = {
                            color: this.color,
                            shape: this.shape,
                            layout: this.layout,
                            size: this.size
                        };

                        // PayPal Credit funding options
                        var funding = {
                            allowed: [],
                            disallowed: []
                        };
                        if (this.offerCredit === true) {
                            funding.allowed.push(paypal.FUNDING.CREDIT);
                        } else {
                            funding.disallowed.push(paypal.FUNDING.CREDIT);
                        }

                        // Disabled function options
                        var disabledFunding = this.disabledFunding;
                        if (true === disabledFunding.card) {
                            funding.disallowed.push(paypal.FUNDING.CARD);
                        }
                        if (true === disabledFunding.elv) {
                            funding.disallowed.push(paypal.FUNDING.ELV);
                        }

                        // Render
                        var actionSuccess = this.actionSuccess;
                        paypal.Button.render({
                            env: this.environment,
                            style: style,
                            funding: funding,

                            payment: function () {
                                return paypalCheckoutInstance.createPayment(data);
                            },

                            onCancel: function (data) {
                                jQuery("#maincontent").trigger('processStop');
                            },

                            onError: function (err) {
                                console.error('paypalCheckout button render error', err);
                                jQuery("#maincontent").trigger('processStop');
                            },

                            /**
                             * Pass the payload (and payload.nonce) through to the implementation's onPaymentMethodReceived method
                             * @param data
                             * @param actions
                             */
                            onAuthorize: function (data, actions) {
                                return paypalCheckoutInstance.tokenizePayment(data)
                                    .then(function (payload) {
                                        jQuery("#maincontent").trigger('processStart');

                                        // Map the shipping address correctly
                                        var address = payload.details.shippingAddress;
                                        payload.details.shippingAddress = {
                                            streetAddress: address.line1,
                                            locality: address.city,
                                            postalCode: address.postalCode,
                                            countryCodeAlpha2: address.countryCode,
                                            email: payload.details.email,
                                            firstname: payload.details.firstName,
                                            lastname: payload.details.lastName,
                                            telephone: typeof payload.details.phone !== 'undefined' ? payload.details.phone : '',
                                            region: typeof address.state !== 'undefined' ? address.state : ''
                                        };

                                        formBuilder.build(
                                            {
                                                action: actionSuccess,
                                                fields: {
                                                    result: JSON.stringify(payload)
                                                }
                                            }
                                        ).submit();
                                    });
                            }
                        }, '#' + this.id);
                    }.bind(this));
                }.bind(this));
            }
        });
    }
);


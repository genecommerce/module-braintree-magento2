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
        'braintreeCheckoutPayPalAdapter',
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
        paypalAdapter,
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
                 * {Bool}
                 */
                fundingicons: null,

                /**
                 * {Bool}
                 */
                branding: null,

                /**
                 * {Bool}
                 */
                tagline: null,

                /**
                 * {String}
                 */
                label: null,

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
                },

                /**
                 * {Object}
                 */
                events: {
                    onClick: null,
                    onCancel: null,
                    onError: null
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

                        let style = {
                            color: this.color,
                            shape: this.shape,
                            size: this.size
                        };

                        if (typeof this.fundingicons === 'boolean') {
                            style.fundingicons = this.fundingicons;
                        }
                        if (typeof this.branding === 'boolean') {
                            style.branding = this.branding;
                        }
                        if (typeof this.label === 'string') {
                            style.label = this.label;
                        }
                        if (typeof this.tagline === 'boolean') {
                            style.tagline = this.tagline;
                        }

                        // PayPal Credit funding options
                        var funding = {
                            allowed: [],
                            disallowed: []
                        };
                        if (this.offerCredit === true) {
                            //funding.allowed.push(paypal.FUNDING.CREDIT);
                            style.label = 'credit'
                        } else {
                            //funding.disallowed.push(paypal.FUNDING.CREDIT);
                        }

                        // Disabled function options
                        var disabledFunding = this.disabledFunding;
                        if (true === disabledFunding.card) {
                            //funding.disallowed.push(paypal.FUNDING.CARD);
                        }
                        if (true === disabledFunding.elv) {
                            funding.disallowed.push(paypal.FUNDING.ELV);
                        }

                        // Render
                        var actionSuccess = this.actionSuccess,
                            beforeSubmit = this.beforeSubmit,
                            events = this.events;

                        paypal.Button.render({
                            env: this.environment,
                            style: style,
                            funding: funding,
                            locale: data.locale,

                            payment: function () {
                                return paypalCheckoutInstance.createPayment(data);
                            },

                            onCancel: function (data) {
                                jQuery("#maincontent").trigger('processStop');

                                if (typeof events.onCancel === 'function') {
                                    events.onCancel();
                                }
                            },

                            onError: function (err) {
                                console.error('paypalCheckout button render error', err);
                                jQuery("#maincontent").trigger('processStop');


                                if (typeof events.onError === 'function') {
                                    events.onError(err);
                                }
                            },

                            onClick: function(data) {
                                if (typeof events.onClick === 'function') {
                                    events.onClick(data);
                                }
                            },

                            /**
                             * Pass the payload (and payload.nonce) through to the implementation's onPaymentMethodReceived method
                             * @param data
                             * @param actions
                             */
                            onAuthorize: function (data, actions) {
                                return paypalCheckoutInstance.tokenizePayment(data)
                                    .then(function (payload) {
                                        if (typeof beforeSubmit === 'function') {
                                            if (!beforeSubmit(payload)) {
                                                return false;
                                            }
                                        }

                                        jQuery("#maincontent").trigger('processStart');

                                        // Map the shipping address correctly
                                        var address = payload.details.shippingAddress;
                                        payload.details.shippingAddress = {
                                            streetAddress: typeof address.line2 !== 'undefined' ? address.line1 + " " + address.line2 : address.line1,
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
                        }, '#' + this.id).then(function (data) {
                            if (typeof events.onRender === 'function') {
                                events.onRender(data);
                            }
                        });
                    }.bind(this));
                }.bind(this));
            },

            beforeSubmit: function () {
                return true;
            }
        });
    }
);


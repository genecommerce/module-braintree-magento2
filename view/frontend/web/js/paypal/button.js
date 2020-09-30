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
                 * {Bool}
                 */
                isRequiredBillingAddress: false,

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

            /**
             * @returns {boolean}
             */
            isBillingAddressRequired: function () {
                return this.isRequiredBillingAddress;
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
                            events = this.events,
                            isBillingAddressRequired = this.isBillingAddressRequired();

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
                                        var recipientName = address.recipientName.split(" ");
                                        payload.details.shippingAddress = {
                                            streetAddress: typeof address.line2 !== 'undefined' ? address.line1.replace(/'/g, "&apos;") + " " + address.line2.replace(/'/g, "&apos;") : address.line1.replace(/'/g, "&apos;"),
                                            locality: address.city.replace(/'/g, "&apos;"),
                                            postalCode: address.postalCode,
                                            countryCodeAlpha2: address.countryCode,
                                            recipientFirstName: recipientName[0].replace(/'/g, "&apos;"),
                                            recipientLastName: recipientName[1].replace(/'/g, "&apos;"),
                                            telephone: typeof payload.details.phone !== 'undefined' ? payload.details.phone : '',
                                            region: typeof address.state !== 'undefined' ? address.state.replace(/'/g, "&apos;") : ''
                                        };
                                        payload.details.email = payload.details.email.replace(/'/g, "&apos;");
                                        payload.details.firstName = payload.details.firstName.replace(/'/g, "&apos;");
                                        payload.details.lastName = payload.details.lastName.replace(/'/g, "&apos;");
                                        if (typeof payload.details.businessName !== 'undefined') {
                                            payload.details.businessName = payload.details.businessName.replace(/'/g, "&apos;");
                                        }

                                        // Map the billing address correctly
                                        if (isBillingAddressRequired === true) {
                                            var billingAddress = payload.details.billingAddress;
                                            payload.details.billingAddress = {
                                                streetAddress: typeof billingAddress.line2 !== 'undefined' ? billingAddress.line1.replace(/'/g, "&apos;") + " " + billingAddress.line2.replace(/'/g, "&apos;") : billingAddress.line1.replace(/'/g, "&apos;"),
                                                locality: billingAddress.city.replace(/'/g, "&apos;"),
                                                postalCode: billingAddress.postalCode,
                                                countryCodeAlpha2: billingAddress.countryCode,
                                                telephone: typeof payload.details.phone !== 'undefined' ? payload.details.phone : '',
                                                region: typeof billingAddress.state !== 'undefined' ? billingAddress.state.replace(/'/g, "&apos;") : ''
                                            };
                                        }

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


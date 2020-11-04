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
        'Magento_Customer/js/customer-data',
        'mage/translate',
        'braintree',
        'braintreeDataCollector',
        'braintreePayPalCheckout',
        'braintreeCheckoutPayPalAdapter',
        'Magento_Braintree/js/form-builder',
        'domReady!'
    ],
    function (
        resolver,
        registry,
        Component,
        _,
        $,
        customerData,
        $t,
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
                 * {String}
                 */
                funding: null,

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
                        displayName: this.displayName
                    };
                this.initCallback(data);
                return this;
            },

            payPalButtons: function(data, paypalCheckoutInstance) {
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

                // Render
                var actionSuccess = this.actionSuccess,
                    beforeSubmit = this.beforeSubmit,
                    events = this.events,
                    paypalActions;
                var button = paypal.Buttons({
                    fundingSource: this.funding,
                    style: style,
                    createOrder: function () {
                        return paypalCheckoutInstance.createPayment(data);
                    },
                    validate: function(actions) {
                        var cart = customerData.get('cart'),
                            customer = customerData.get('customer'),
                            declinePayment = false,
                            isGuestCheckoutAllowed;
                        isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;
                        declinePayment = !customer().firstname && !isGuestCheckoutAllowed;
                        if (declinePayment) {
                            actions.disable();
                        }
                        paypalActions = actions;
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

                        var cart = customerData.get('cart'),
                            customer = customerData.get('customer'),
                            declinePayment = false,
                            isGuestCheckoutAllowed;
                        isGuestCheckoutAllowed = cart().isGuestCheckoutAllowed;
                        declinePayment = !customer().firstname && !isGuestCheckoutAllowed && (typeof isGuestCheckoutAllowed !== 'undefined');
                        if (declinePayment) {
                            alert($t('To check out, please sign in with your email address.'));
                        }

                        if (typeof events.onClick === 'function') {
                            events.onClick(data);
                        }
                    },

                    onApprove: function (data)  {
                        return paypalCheckoutInstance.tokenizePayment(data, function (err, payload) {
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
                                email: payload.details.email.replace(/'/g, "&apos;"),
                                firstname: recipientName[0].replace(/'/g, "&apos;"),
                                lastname: recipientName[1].replace(/'/g, "&apos;"),
                                telephone: typeof payload.details.phone !== 'undefined' ? payload.details.phone : '',
                                region: typeof address.state !== 'undefined' ? address.state.replace(/'/g, "&apos;") : ''
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
                });
                if (!button.isEligible()) {
                    jQuery('#' + this.id).parent().remove();
                    return;
                }
                button.render('#' + this.id);
            },
            initCallback: function (data) {
                if (typeof paypal !== 'undefined' ) {
                    this.payPalButtons(data);
                } else {
                    braintree.create({
                        authorization: this.clientToken
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
                        }, function (err, paypalCheckoutInstance) {

                            paypalCheckoutInstance.loadPayPalSDK({
                                components: 'buttons,messages',
                                "buyer-country": 'US',
                            }, function () {
                                this.payPalButtons(data, paypalCheckoutInstance);
                            }.bind(this));
                        }.bind(this));
                    }.bind(this));
                }
            },

            beforeSubmit: function () {
                return true;
            }
        });
    }
);
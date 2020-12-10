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
        formBuilder
    ) {
        'use strict';
        let buttonIds = [];


        return {
            events: {
                onClick: null,
                onCancel: null,
                onError: null
            },

            init: function (token) {
                buttonIds = [];
                $('.action-braintree-paypal-logo').each(function () {
                    if(!$(this).hasClass( "button-loaded" )) {
                        $(this).addClass('button-loaded');
                        buttonIds.push($(this).attr('id'));
                    }
                });

                if(buttonIds.length > 0){
                    this.loadSDK(token);
                }
            },

            loadSDK: function (token) {
                braintree.create({
                    authorization: token
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

                        if (typeof paypal !== 'undefined' ) {
                            this.renderpayPalButtons(buttonIds, paypalCheckoutInstance);
                            this.renderpayPalMessages();
                        } else {
                            paypalCheckoutInstance.loadPayPalSDK({
                                components: 'buttons,messages,funding-eligibility',
                            }, function () {
                                this.renderpayPalButtons(buttonIds, paypalCheckoutInstance);
                                this.renderpayPalMessages();
                            }.bind(this));
                        }


                    }.bind(this));
                }.bind(this));
            },
            renderpayPalButtons: function(ids, paypalCheckoutInstance) {
                _.each(ids,function(id) {
                    this.payPalButton(id, paypalCheckoutInstance);

                }.bind(this));
            },

            renderpayPalMessages: function() {
                $('.action-braintree-paypal-message').each(function () {
                    paypal.Messages({
                        amount: $(this).data('pp-amount'),
                        pageType: $(this).data('pp-type'),
                        style: {
                            layout: 'text',
                        }
                    }).render('#' + $(this).attr('id'));


                });
            },

            payPalButton: function(id, paypalCheckoutInstance) {

                let data = $('#' + id);
                let style = {
                    color: data.data('color'),
                    shape: data.data('shape'),
                    size: data.data('size'),
                };

                if (data.data('fundingicons')) {
                    style.fundingicons = data.data('fundingicons');
                }

                // Render
                var paypalActions;
                var button = paypal.Buttons({
                    fundingSource: data.data('funding'),
                    style: style,
                    createOrder: function () {
                        return paypalCheckoutInstance.createPayment(
                            {
                                amount: data.data('amount'),
                                locale: data.data('locale'),
                                currency: data.data('currency'),
                                flow: 'checkout',
                                enableShippingAddress: true,
                                displayName: data.data('displayname')
                            });
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

                        /*if (typeof events.onCancel === 'function') {
                            events.onCancel();
                        }*/
                    },

                    onError: function (err) {
                        console.error('paypalCheckout button render error', err);
                        jQuery("#maincontent").trigger('processStop');


                        /*if (typeof events.onError === 'function') {
                            events.onError(err);
                        }*/
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

                        /*if (typeof events.onClick === 'function') {
                            events.onClick(data);
                        }*/
                    },

                    onApprove: function (data1)  {
                        return paypalCheckoutInstance.tokenizePayment(data1, function (err, payload) {
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
                            if(data.data('location') == 'productpage') {
                                var form = $("#product_addtocart_form");
                                if (!(form.validation() && form.validation('isValid'))) {
                                    return false;
                                }
                                payload.additionalData = form.serialize();
                            }

                            var actionSuccess = data.data('actionsuccess');
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
                    data.parent().remove();
                    return;
                }
                button.render('#' + data.attr('id'));
            },
        }
    }
);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'braintree',
    'braintreePayPalCheckout',
    'Magento_Ui/js/modal/alert',
    'domReady!'
], function (_, $, braintree, paypalCheckout, alert) {
    'use strict';
    let buttonIds = [];

    return {
        events: {
            onClick: null
        },

        /**
         * Initialize button
         *
         * @param buttonConfig
         */
        init: function (buttonConfig) {
            buttonIds = [];
            $('.action-braintree-paypal-logo').each(function () {
                if (!$(this).hasClass("button-loaded")) {
                    $(this).addClass('button-loaded');
                    buttonIds.push($(this).attr('id'));
                }
            });

            if (buttonIds.length > 0) {
                this.loadSDK(buttonConfig);
            }
        },

        /**
         * Load Braintree PayPal SDK
         *
         * @param buttonConfig
         */
        loadSDK: function (buttonConfig) {
            let self = this;

            braintree.create({
                authorization: buttonConfig.clientToken
            }, function (clientErr, clientInstance) {
                if (clientErr) {
                    console.error('paypalCheckout error', clientErr);
                    return self.showError("PayPal Checkout could not be initialized. Please contact the store owner.");
                }
                paypalCheckout.create({
                    client: clientInstance
                }, function (err, paypalCheckoutInstance) {
                    if (typeof paypal !== 'undefined' ) {
                        this.renderPayPalButtons(buttonIds);
                        this.renderPayPalMessages();
                    } else {
                        let configSDK = {
                            components: 'buttons,messages,funding-eligibility',
                            "enable-funding": (this.isCreditActive(buttonConfig)) ? 'credit' : 'paylater',
                            currency: buttonConfig.currency
                        };

                        let buyerCountry = this.getMerchantCountry(buttonConfig);
                        if (buttonConfig.environment === 'sandbox'
                            && (buyerCountry !== '' || buyerCountry !== 'undefined'))
                        {
                            configSDK["buyer-country"] = buyerCountry;
                        }
                        paypalCheckoutInstance.loadPayPalSDK(configSDK, function () {
                            this.renderPayPalButtons(buttonIds);
                            this.renderPayPalMessages();
                        }.bind(this));
                    }
                }.bind(this));
            }.bind(this));
        },

        /**
         * Is Credit enabled
         *
         * @param buttonConfig
         * @returns {boolean}
         */
        isCreditActive: function (buttonConfig) {
            return buttonConfig.isCreditActive;
        },

        /**
         * Get merchant country
         *
         * @param buttonConfig
         * @returns {string}
         */
        getMerchantCountry: function (buttonConfig) {
            return buttonConfig.merchantCountry;
        },

        /**
         * Render PayPal buttons
         * @param ids
         */
        renderPayPalButtons: function (ids) {
            _.each(ids, function (id) {
                this.payPalButton(id);
            }.bind(this));
        },

        /**
         * Render PayPal messages
         */
        renderPayPalMessages: function () {
            $('.action-braintree-paypal-message').each(function () {
                let messages = paypal.Messages({
                    amount: $(this).data('pp-amount'),
                    pageType: $(this).data('pp-type'),
                    style: {
                        layout: $(this).data('messaging-layout'),
                        text: {
                            color:   $(this).data('messaging-text-color')
                        },
                        logo: {
                            type: $(this).data('messaging-logo'),
                            position: $(this).data('messaging-logo-position')
                        }
                    }
                });

                if ($('#' + $(this).attr('id')).length && $(this).data('messaging-show')) {
                    messages.render('#' + $(this).attr('id'));
                }
            });
        },

        /**
         * @param id
         */
        payPalButton: function (id) {
            let buttonElement = $('#' + id);
            let style = {
                label: buttonElement.data('label'),
                color: buttonElement.data('color'),
                shape: buttonElement.data('shape'),
                size: buttonElement.data('size')
            };

            if (buttonElement.data('fundingicons')) {
                style.fundingicons = buttonElement.data('fundingicons');
            }

            // Render
            let button = paypal.Buttons({
                fundingSource: buttonElement.data('funding'),
                style: style,

                onInit: function (data, actions) {
                    actions.disable();
                }
            });
            if (!button.isEligible()) {
                console.log(buttonElement.data('funding').charAt(0).toUpperCase() + buttonElement.data('funding').slice(1).toLowerCase() + ' button is not eligible');
                buttonElement.parent().remove();
                return;
            }
            if ($('#' + buttonElement.attr('id')).length && buttonElement.data('show')) {
                button.render('#' + buttonElement.attr('id'));
            }
        },

        /**
         * Show alert message
         * @param {String} message
         */
        showError: function (message) {
            alert({
                content: message
            });
        }
    }
});

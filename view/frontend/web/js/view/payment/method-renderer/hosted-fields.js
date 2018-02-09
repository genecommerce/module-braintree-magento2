/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/

define([
    'jquery',
    'Magento_Braintree/js/view/payment/method-renderer/cc-form',
    'Magento_Braintree/js/validator',
    'Magento_Vault/js/view/payment/vault-enabler',
    'mage/translate'
], function ($, Component, validator, VaultEnabler, $t) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Magento_Braintree/payment/form',
            clientConfig: {

                /**
                 * {String}
                 */
                id: 'co-transparent-form-braintree'
            },
            isValidCardNumber: false,
            isValidExpirationDate: false,
            isValidCvvNumber: false,

            onInstanceReady: function (instance) {
                instance.on('validityChange', this.onValidityChange.bind(this));
            }
        },

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());

            return this;
        },

        /**
         * Init config
         */
        initClientConfig: function () {
            this._super();

            this.clientConfig.hostedFields = this.getHostedFields();
            this.clientConfig.onInstanceReady = this.onInstanceReady.bind(this);
        },

        /**
         * @returns {Object}
         */
        getData: function () {
            var data = this._super();

            this.vaultEnabler.visitAdditionalData(data);

            return data;
        },

        /**
         * @returns {Bool}
         */
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        /**
         * Get Braintree Hosted Fields
         * @returns {Object}
         */
        getHostedFields: function () {
            var self = this,
                fields = {
                    number: {
                        selector: self.getSelector('cc_number'),
                        placeholder: $t('4111 1111 1111 1111')
                    },
                    expirationDate: {
                        selector: self.getSelector('expirationDate'),
                        placeholder: $t('MM/YYYY')
                    }
                };

            if (self.hasVerification()) {
                fields.cvv = {
                    selector: self.getSelector('cc_cid'),
                    placeholder: $t('123')
                };
            }

            return fields;
        },

        /**
         * Triggers on Hosted Field changes
         * @param {Object} event
         * @returns {Boolean}
         */
        onValidityChange: function (event) {
            // Handle a change in validation or card type
            if (event.emittedBy === 'number') {
                this.selectedCardType(null);

                if (event.cards.length === 1) {
                    this.isValidCardNumber = event.fields.number.isValid;
                    this.selectedCardType(
                        validator.getMageCardType(event.cards[0].type, this.getCcAvailableTypes())
                    );
                    this.validateCardType();
                }
            }

            // Other field validations
            this.isValidExpirationDate = event.fields.expirationDate.isValid;
            this.isValidCvvNumber = event.fields.cvv.isValid;
        },

        /**
         * Toggle invalid class on selector
         * @param selector
         * @param state
         * @returns {boolean}
         */
        validateField: function (selector, state) {
            var $selector = $(this.getSelector(selector)),
                invalidClass = 'braintree-hosted-fields-invalid';

            if (state === true) {
                $selector.removeClass(invalidClass);
                return true;
            }

            $selector.addClass(invalidClass);
            return false;
        },

        /**
         * Validate current credit card type
         * @returns {Boolean}
         */
        validateCardType: function () {
            return this.validateField(
                'cc_number',
                (this.selectedCardType() !== null && this.isValidCardNumber)
            );
        },

        /**
         * Validate current expiry date
         * @returns {boolean}
         */
        validateExpirationDate: function () {
            return this.validateField(
                'expirationDate',
                (this.isValidExpirationDate === true)
            );
        },

        /**
         * Validate current CVV field
         * @returns {boolean}
         */
        validateCvvNumber: function () {
            return this.validateField(
                'cc_cid',
                (this.isValidCvvNumber === true)
            );
        },

        /**
         * Validate all fields
         * @returns {boolean}
         */
        validateFormFields: function () {
            return (this.validateCardType() && this.validateExpirationDate() && this.validateCvvNumber()) === true;
        },

        /**
         * Trigger order placing
         */
        placeOrderClick: function () {
            if (this.validateFormFields()) {
                this.placeOrder();
            }
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
        }
    });
});

/**
 * Braintree Google Pay mini cart payment method integration.
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
define(
    [
        'uiComponent',
        'Magento_Braintree/js/googlepay/button',
        'Magento_Braintree/js/googlepay/api',
        'mage/translate',
        'domReady!'
    ],
    function (
        Component,
        button,
        buttonApi,
        $t
    ) {
        'use strict';

        return Component.extend({

            defaults: {
                id: null,
                clientToken: null,
                merchantId: null,
                currencyCode: null,
                actionSuccess: null,
                amount: null,
                environment: "TEST",
                cardType: []
            },

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                var api = new buttonApi();
                api.setEnvironment(this.environment);
                api.setCurrencyCode(this.currencyCode);
                api.setClientToken(this.clientToken);
                api.setMerchantId(this.merchantId);
                api.setActionSuccess(this.actionSuccess);
                api.setAmount(this.amount);
                api.setCardTypes(this.cardTypes);

                // Attach the button
                button.init(
                    document.getElementById(this.id),
                    api
                );

                return this;
            }
        });
    }
);

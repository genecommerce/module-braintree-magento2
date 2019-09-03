var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'Magento_Braintree/js/model/step-navigator-mixin': true
            }
        }
    },
    map: {
        '*': {
            braintreeCheckoutPayPalAdapter: 'Magento_Braintree/js/view/payment/adapter'
        }
    },
};

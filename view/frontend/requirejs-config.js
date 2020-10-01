var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'Magento_Braintree/js/model/step-navigator-mixin': true
            },
            'PayPal_Braintree/js/view/payment/method-renderer/cc-form': {
                'PayPal_Braintree/js/reCaptcha/braintree-cc-method-mixin': true
            }
        }
    },
    map: {
        '*': {
            braintreeCheckoutPayPalAdapter: 'Magento_Braintree/js/view/payment/adapter'
        }
    },
};

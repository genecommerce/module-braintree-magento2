define(
    [
        'jquery'
    ], function (
        $
    ) {
        'use strict';
        $.widget('braintree.fallback', {
            _create: function () {
                console.log(this.options.data)
            }
        });
        return $.braintree.fallback;
    }
);

define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    var mixin = {
        handleHash: function (originalFn) {
            console.log('foo');

            let hashString = window.location.hash.replace('#', '');

            if ($.inArray(hashString, 'venmo')) {
                return false;
            } else {
                originalFn();
            }
        }
    };

    return function (target) {
        return wrapper.extend(target, mixin);
    };
});
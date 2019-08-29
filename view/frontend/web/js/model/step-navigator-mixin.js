define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    let mixin = {
        handleHash: function (originalFn) {
            let hashString = window.location.hash.replace('#', '');

            if (hashString.indexOf('venmo') >= 0) {
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

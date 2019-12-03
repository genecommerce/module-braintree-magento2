define([
    'mage/utils/wrapper',
    'jquery'
], function (wrapper, $) {
    'use strict';

    var mixin = {
        handleHash: function (originalFn) {
            var hashString = window.location.hash.replace('#', '');
            return (hashString.includes('venmo')) ? false : originalFn();
        }
    };

    return function (target) {
        return wrapper.extend(target, mixin);
    };
});

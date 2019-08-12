define(['jquery'],function ($) {
        'use strict';

        function initShopping(config) {
            let tracker = paypal.Tracker({});
            $(document).on('ajax:addToCart', function (event, data) {
                console.log(data.form.data());
                let productData = data.form.data();
                tracker.addToCart({
                    items: [{
                        id: productData.productSku,
                        title: 'Product Title',
                        description: 'A lovely item to buy',
                        url: 'https://example.com/myitemid',
                        imgUrl: 'https://example.com/images/items/myitemid.jpg',
                        price: '99.99'
                    }],
                    total: "99.99"
                });
            });
        }

        return initShopping;
    }
);
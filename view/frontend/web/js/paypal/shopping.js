define(
    [
        'jquery'
    ],
    function (
        $
    ) {
        'use strict';

        const encodeData = data => encodeURIComponent(btoa(JSON.stringify(data)));

        const initTracker = config => {
            return paypal.Tracker({
                propertyId: config.propertyId,
                paramsToBeaconUrl: params => {
                    const { trackingType, data } = params;
                    return `https://www.sandbox.paypal.com/targeting/track/${ trackingType }?data=${ encodeData(data) }`
                },
                paramsToTokenUrl: () => 'https://www.sandbox.paypal.com'
            })
        };

        const initShopping = config => {
            let tracker = initTracker(config);

            $(document).on('ajax:addToCart', function (event, data) {
                let productData = data.form.data();
                tracker.setCart({
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
        };

        return initShopping(config);
    }
);
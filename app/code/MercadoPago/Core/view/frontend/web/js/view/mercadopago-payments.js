define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'mercadopago_standard',
                component: 'MercadoPago_Core/js/view/method-renderer/standard-method'
            }
        );

        return Component.extend({});
    }
);

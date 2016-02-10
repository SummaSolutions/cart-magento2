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
                component: 'MercadoPago_Core/js/view/standard/renderer'
            }
        );

        return Component.extend({});
    }
);

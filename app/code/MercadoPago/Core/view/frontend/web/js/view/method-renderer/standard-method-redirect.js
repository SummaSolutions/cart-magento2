
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'MPcheckout',
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MercadoPago_Core/payment/standard_redirect',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
            isPaymentReady: function () {
                return this.paymentReady();
            },
            /**
             * Get action url for payment method.
             * @returns {String}
             */
            getActionUrl: function () {
                if (window.checkoutConfig.payment['mercadopago_standard'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_standard']['actionUrl'];
                }
                return '';
            },

            getBannerUrl: function () {
                if (window.checkoutConfig.payment['mercadopago_standard'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_standard']['bannerUrl'];
                }
                return '';
            },

            /**
             * Places order in pending payment status.
             */
            placePendingPaymentOrder: function () {
                if (this.placeOrder()) {
                    window.location = this.getActionUrl();
                }
            },
        });
    }
);

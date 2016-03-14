
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'MPcheckout',
        'jquery',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'meli',
        'tinyj',
        'MPcustom'
    ],
    function (Component) {
        'use strict';

        var configPayment = window.checkoutConfig.payment.mercadopago_custom_ticket;

        return Component.extend({
            defaults: {
                template: 'MercadoPago_Core/payment/custom_ticket',
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
                if (configPayment != undefined) {
                    return configPayment['actionUrl'];
                }
                return '';
            },

            getBannerUrl: function () {
                if (configPayment != undefined) {
                    return configPayment['bannerUrl'];
                }
                return '';
            },

            getTicketsData: function () {
                return configPayment['options'];
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

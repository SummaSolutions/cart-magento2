
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

        var configPayment = window.checkoutConfig.payment.mercadopago_customticket;

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

            getCode: function () {
                return 'mercadopago_customticket';
            },

            getTicketsData: function () {
                return configPayment['options'];
            },

            getCountTickets: function () {
                var options = this.getTicketsData();
                return options.length;
            },

            getFirstTicketId: function () {
                var options = this.getTicketsData();
                return options[0]['id'];
            },

            getGrandTotal: function () {
                if (configPayment != undefined) {
                    return configPayment['grand_total'];
                }
                return '';
            },
            getCountry: function () {
                if (configPayment != undefined) {
                    return configPayment['country'];
                }
                return '';
            },

            getBaseUrl: function () {
                if (configPayment != undefined) {
                    return configPayment['base_url'];
                }
                return '';
            },
            getRoute: function () {
                if (configPayment != undefined) {
                    return configPayment['route'];
                }
                return '';
            },

            getSuccessUrl: function () {
                if (configPayment != undefined) {
                    return configPayment['success_url'];
                }
                return '';
            },

            afterPlaceOrder : function () {
                window.location = this.getSuccessUrl();
            }
        });
    }
);

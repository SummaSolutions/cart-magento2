
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
            placeOrderHandler: null,
            validateHandler: null,
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            isPaymentReady: function () {
                return this.paymentReady();
            },

            context: function () {
                return this;
            },

            isShowLegend: function () {
                return true;
            },

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
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

            getPaymentSelected: function() {
                if (this.getCountTickets()==1) {
                    var option = TinyJ('.optionsTicketMp');
                    return option.val();
                }
                var options = TinyJ('.optionsTicketMp');
                if (options.length > 0) {
                    for (var i = 0; i < options.length; i++) {
                        option = options[i];
                        if (option.isChecked()){
                            return option.val();
                        }
                    }
                }
                return false;
            },

            getSuccessUrl: function () {
                if (configPayment != undefined) {
                    return configPayment['success_url'];
                }
                return '';
            },

            /**
             * @override
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'method': this.getCode(),
                        'coupon_code': '',
                        'payment_method_ticket':this.getPaymentSelected(),
                        'total_amount':  TinyJ('#mercadopago_checkout_custom_ticket .total_amount').val(),
                        'amount': TinyJ('#mercadopago_checkout_custom_ticket .amount').val(),
                        'mercadopago-discount-amount': TinyJ('#mercadopago_checkout_custom_ticket .mercadopago-discount-amount').val(),
                        'site_id': this.getCountry(),
                    }
                };
            },

            afterPlaceOrder : function () {
                window.location = this.getSuccessUrl();
            },

            validate : function () {
                return this.validateHandler();
            }
        });
    }
);

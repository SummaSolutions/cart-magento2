define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/iframe',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/full-screen-loader',
        'meli',
        'tinyj',
        'MPcustom'
    ],
    function ($, Component, additionalValidators, setPaymentInformationAction, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MercadoPago_Core/payment/custom-method'
            },
            placeOrderHandler: null,
            validateHandler: null,

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            context: function () {
                return this;
            },

            isShowLegend: function () {
                return true;
            },

            getCode: function () {
                return 'mercadopago_custom';
            },

            getTokenCodeArray: function (code) {
                return "payment[" + this.getCode() + "][" + code + "]";
            },

            getMethodCodeArray: function () {
                return "payment[" + this.getCode() + "][payment_method_id]";
            },
            getInstallmentsCodeArray: function () {
                return "payment[" + this.getCode() + "][installments]";
            },

            isActive: function () {
                return true;
            },

            initApp: function () {
                console.log('Initializing MP......');
                window.PublicKeyMercadoPagoCustom = window.checkoutConfig.payment['mercadopago_custom']['public_key'];
                MercadoPagoCustom.enableLog(window.checkoutConfig.payment['mercadopago_custom']['logEnabled']);
                MercadoPagoCustom.getInstance().init();
            },
            getBannerUrl: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['bannerUrl'];
                }
                return '';
            },
            getGrandTotal: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['grand_total'];
                }
                return '';
            },
            getBaseUrl: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['base_url'];
                }
                return '';
            },
            getRoute: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['route'];
                }
                return '';
            },
            getCountry: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['country'];
                }
                return '';
            },

            /**
             * @override
             */
            placeOrder: function () {
                var self = this;

                //if (this.validateHandler() && additionalValidators.validate()) {
                //    fullScreenLoader.startLoader();
                //    this.isPlaceOrderActionAllowed(false);
                //    $.when(setPaymentInformationAction(this.messageContainer, {
                //        'method': self.getCode()
                //    })).done(function () {
                //        self.placeOrderHandler().fail(function () {
                //            fullScreenLoader.stopLoader();
                //        });
                //    }).fail(function () {
                //        fullScreenLoader.stopLoader();
                //        self.isPlaceOrderActionAllowed(true);
                //    });
                //}
            }
        });
    }
);

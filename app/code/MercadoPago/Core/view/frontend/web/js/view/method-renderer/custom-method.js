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

            isOCPReady: function () {
                //if ($customer !== false && isset($customer['cards']) && count($customer['cards']) > 0)
                return false;
            },
            getTotalAmount: function () {
                console.log('xxx ' + this.totalAmount() + 'xxxx')
            },

            initApp: function () {
                console.log('Initializing MP......');
                window.PublicKeyMercadoPagoCustom = window.checkoutConfig.payment['mercadopago_custom']['public_key'];
                MercadoPagoCustom.enableLog(window.checkoutConfig.payment['mercadopago_custom']['logEnabled']);
                MercadoPagoCustom.getInstance().init();
                //MercadoPagoCustom.getInstance().initOCP();
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
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'payment[method]': this.getCode(),
                        'coupon_code': '',
                        'card_expiration_month': TinyJ('#cardExpirationMonth').val(),
                        'card_expiration_year': TinyJ('#cardExpirationYear').val(),
                        'card_holder_name': TinyJ('#cardholderName').val(),
                        'doc_type': TinyJ('#docType').val(),
                        'doc_number': TinyJ('#docNumber').val(),
                        'installments': TinyJ('#installments').val(),
                        'total_amount':  TinyJ('.total_amount').val(),
                        'amount': TinyJ('#cardExpirationMonth').val(),
                        'mercadopago-discount-amount': TinyJ('#cardExpirationMonth').val(),
                        'site_id': this.getCountry(),
                        'token': TinyJ('.token').val(),
                        'payment_method_id': TinyJ('.payment_method_id').val(),
                        'one_click_pay': TinyJ('#one_click_pay_mp').val()
                    }
                };
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'totalAmount'
                    ]);
                return this;
            },

            ///**
            // * @override
            // */
            //placeOrder: function () {
            //    var self = this;
            //
            //    //if (this.validateHandler() && additionalValidators.validate()) {
            //        fullScreenLoader.startLoader();
            //        this.isPlaceOrderActionAllowed(false);
            //        $.when(setPaymentInformationAction(this.messageContainer, {
            //            'method': self.getCode()
            //        })).done(function () {
            //            self.placeOrderHandler().fail(function () {
            //                fullScreenLoader.stopLoader();
            //            });
            //        }).fail(function () {
            //            fullScreenLoader.stopLoader();
            //            self.isPlaceOrderActionAllowed(true);
            //        });
            //    //}
            //}
        });
    }
);

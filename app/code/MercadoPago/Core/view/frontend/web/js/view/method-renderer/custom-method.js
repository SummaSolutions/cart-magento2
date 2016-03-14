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
            redirectAfterPlaceOrder: false,


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

            isActive: function () {
                return true;
            },

            isOCPReady: function () { return true;
                //if ($customer !== false && isset($customer['cards']) && count($customer['cards']) > 0)
                return (this.getCustomer() != false);
            },

            initApp: function () {
                window.PublicKeyMercadoPagoCustom = window.checkoutConfig.payment['mercadopago_custom']['public_key'];
                MercadoPagoCustom.enableLog(window.checkoutConfig.payment['mercadopago_custom']['logEnabled']);
                MercadoPagoCustom.getInstance().init();
                if (this.isOCPReady()){
                    MercadoPagoCustom.getInstance().initOCP();
                }
            },
            getAvailableCards: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    var _customer = window.checkoutConfig.payment['mercadopago_custom']['customer'];
                    var Card = function(value, name, firstSix, securityCodeLength) {
                        this.cardName = name;
                        this.value = value;
                        this.firstSix = firstSix;
                        this.securityCodeLength = securityCodeLength;
                    };
                    var availableCards = [];
                    _customer.cards.forEach(function(card) {
                        availableCards.push(new Card(card['id'], card['payment_method']['name']+ ' ended in ' + card['last_four_digits'], card['first_six_digits'], card['security_code']['length'] ));
                    });
                    return availableCards;
                }
                return [];
            },
            setOptionsExtraValues: function (option, item) {
                jQuery(option).attr('first_six_digits', item.firstSix);
                jQuery(option).attr('security_code_length', item.securityCodeLength);
            },
            getCustomerAttribute: function (attribute) {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['customer'][attribute];
                }
                return '';
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
            getSuccessUrl: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['success_url'];
                }
                return '';
            },
            getCustomer: function () {
                if (window.checkoutConfig.payment['mercadopago_custom'] != undefined) {
                    return window.checkoutConfig.payment['mercadopago_custom']['customer'];
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
                        'mercadopago-discount-amount': TinyJ('.mercadopago-discount-amount').val(),
                        'site_id': this.getCountry(),
                        'token': TinyJ('.token').val(),
                        'payment_method_id': TinyJ('.payment_method_id').val(),
                        'one_click_pay': TinyJ('#one_click_pay_mp').val(),
                        'customer_id': TinyJ('#customer_id').val(),
                        'issuer_id': TinyJ('#issuer').val()
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

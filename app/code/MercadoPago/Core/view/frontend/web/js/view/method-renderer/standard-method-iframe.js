define(
    [
        'Magento_Checkout/js/view/payment/default',
        'ko',
        'MercadoPago_Core/js/model/iframe',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (Component, ko, iframe, fullScreenLoader) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MercadoPago_Core/payment/standard-iframe',
                paymentReady: false
            },
            redirectAfterPlaceOrder: false,
            isInAction: iframe.isInAction,
            initObservable: function () {
                this._super()
                    .observe('paymentReady');

                return this;
            },
            isPaymentReady: function () {
                return this.paymentReady();
            },
            /**
             * Get action url for payment method iframe.
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
                var self = this;
                this.afterPlaceOrder = function () {
                    self.paymentReady(true);
                };
                if (this.placeOrder()) {
                    this.isInAction(true);
                    // capture all click events
                    document.addEventListener('click', iframe.stopEventPropagation, true);
                }
            },
            /**
             * Hide loader when iframe is fully loaded.
             * @returns {void}
             */
            iframeLoaded: function () {
                fullScreenLoader.stopLoader();
            }
        });
    }
);

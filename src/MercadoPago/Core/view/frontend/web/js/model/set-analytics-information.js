/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'jquery',
        'Magento_Customer/js/model/customer'
    ],
    function ($, customer) {
        return {
            beforePlaceOrder: function (code) {
                if (window.checkoutConfig.payment[code] != undefined) {
                    var MA = ModuleAnalytics;
                    if (window.checkoutConfig.payment[code]['public_key'] != '') {
                        MA.setPublicKey(window.checkoutConfig.payment[code]['public_key']);
                    } else {
                        MA.setToken(window.checkoutConfig.payment[code]['analytics_key']);
                    }
                    MA.setPlatform("Magento");
                    MA.setPlatformVersion(window.checkoutConfig.payment[code]['platform_version']);
                    MA.setModuleVersion(window.checkoutConfig.payment[code]['module_version']);
                    MA.setPayerEmail(customer.isLoggedIn() ? window.checkoutConfig.payment[code]['customer']['email'] : '');
                    MA.setUserLogged(customer.isLoggedIn() ? 1 : 0);
                    MA.setInstalledModules("magento_mercadopago_module");
                    MA.post();
                }
            },
            afterPlaceOrder: function (code) {
                if (window.checkoutConfig.payment[code] != undefined) {
                    var MA = ModuleAnalytics;
                    MA.setPublicKey(window.checkoutConfig.payment[code]['public_key']);
                    MA.setPaymentType("credit_card");
                    MA.setCheckoutType("custom");
                    MA.put();
                }
            }
        }
    }
);

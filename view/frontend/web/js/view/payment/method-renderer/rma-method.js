define([
    'ko',
    'Magento_Checkout/js/view/payment/default'
], function (ko, Component) {
    'use strict';

    return Component.extend({
        isPlaceOrderActionAllowed: ko.observable(true),
        defaults: {
            template: 'Zero1_OpenPosRma/payment/rma',
            selectedRmaMethod: null
        },

        initObservable: function () {
            this._super()
                .observe(['selectedRmaMethod']);
            return this;
        },

        getRmaOptions: function () {
            var options = window.checkoutConfig.payment.openpos_rma.options;

            return _.map(options, function(label, code) {
                return {
                    'code': code,
                    'label': label
                };
            });
        },

        validate: function () {
            var isValid = this._super();
            if (!this.selectedRmaMethod()) {
                return false;
            }
            return isValid;
        },

        getData: function () {
            var selectedCode = this.selectedRmaMethod();
            
            var rawOptions = window.checkoutConfig.payment.openpos_pay_rma.options;
            var selectedTitle = rawOptions[selectedCode] ? rawOptions[selectedCode] : '';

            return {
                'method': this.item.method,
                'additional_data': {
                    'openpos_rma_method_code': selectedCode,
                    'openpos_rma_method_title': selectedTitle
                }
            };
        }
    });
});
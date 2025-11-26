define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'openpos_rma',
            component: 'Zero1_OpenPosRma/js/view/payment/method-renderer/rma-method'
        }
    );

    return Component.extend({});
});

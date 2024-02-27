<?php

namespace Zero1\OpenPosRma\Plugin;

use Magento\Payment\Model\Checks\ZeroTotal;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class ZeroTotalBypass
{
    /**
     * Check whether payment method is applicable to quote
     * Purposed to allow use in controllers some logic that was implemented in blocks only before
     *
     * @param ZeroTotal $zeroTotal
     * @param MethodInterface $paymentMethod
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $result
     * @return bool
     */
    public function afterIsApplicable(ZeroTotal $zeroTotal, bool $result, MethodInterface $paymentMethod, Quote $quote)
    {
        if($paymentMethod->getCode() === 'openpos_rma') {
            return true;
        }

        return $result;
    }
}

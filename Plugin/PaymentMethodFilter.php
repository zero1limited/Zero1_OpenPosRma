<?php
declare(strict_types=1);

namespace Zero1\OpenPosRma\Plugin;

use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;

class PaymentMethodFilter
{
    const OPENPOS_RMA_METHOD_CODE = 'openpos_rma';

    /**
     * @var OpenPosHelper
     */
    protected $openPosHelper;

    /**
     * @param OpenPosHelper $posHelper
     */
    public function __construct(
        OpenPosHelper $openPosHelper
    ) {
        $this->openPosHelper = $openPosHelper;
    }

    public function afterIsAvailable(MethodInterface $subject, $result, CartInterface $quote = null)
    {
        if (!$quote) {
            return $result;
        }

        // Only apply logic if on OpenPOS store
        if (!$this->openPosHelper->currentlyOnPosStore()) {
            return $result;
        }

        $isRmaCart = $quote->getGrandTotal() <= 0;
        $isRmaMethod = $subject->getCode() === self::OPENPOS_RMA_METHOD_CODE;

        // If RMA cart, only allow RMA method
        if ($isRmaCart) {
            return $isRmaMethod;
        }

        // If not RMA cart, allow all methods except RMA method
        if ($isRmaMethod) {
            return false;
        }

        return $result;
    }
}
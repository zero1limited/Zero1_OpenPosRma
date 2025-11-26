<?php
declare(strict_types=1);

namespace Zero1\OpenPosRma\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Zero1\OpenPos\Model\Payment\MethodProvider;

class RmaConfigProvider implements ConfigProviderInterface
{
    /**
     * @var MethodProvider
     */
    protected $methodProvider;

    /**
     * @param MethodProvider $methodProvider
     */
    public function __construct(
        MethodProvider $methodProvider
    ) {
        $this->methodProvider = $methodProvider;
    }

    public function getConfig(): array
    {
        $paymentMethods = [];
        $allPaymentMethods = $this->methodProvider->getAll();
        foreach($allPaymentMethods as $paymentMethod) {
            if(isset($paymentMethod['canUseForRma']) && $paymentMethod['canUseForRma'] == true) {
                $paymentMethods[$paymentMethod['code']] = $paymentMethod['label'];
            }
        }

        return [
            'payment' => [
                'openpos_rma' => [
                    'options' => $paymentMethods
                ]
            ]
        ];
    }
}
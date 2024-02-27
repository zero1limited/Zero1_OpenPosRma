<?php

declare(strict_types=1);

namespace Zero1\OpenPosRma\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Payment\Helper\Data as PaymentHelper;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;

class RmaMethod extends Component implements EvaluationInterface
{
    const OPENPOS_RMA_EXCLUDED_METHODS = [
        'openpos_rma',
        'openpos_split_payment'
    ];

    public $loader = [
        'save' => 'Applying...'
    ];

    public $listeners = [
        'save'
    ];

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var array
     */
    public $paymentMethods = [];

    /**
     * @var bool
     */
    public $applied = false;


    /**
     * @param CheckoutSession $checkoutSession
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        PaymentHelper $paymentHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->paymentHelper = $paymentHelper;
    }

    public function mount(): void
    {
        $this->getPaymentMethods();
        parent::mount();
    }

    /**
     * Retrieve a list of payment methods available for refund use.
     *
     * @return void
     */
    public function getPaymentMethods(): void
    {
        $paymentMethods = $this->paymentHelper->getPaymentMethods();

        foreach($paymentMethods as $code => $paymentMethod) {
            if(strpos($code, 'openpos') === false) {
                continue;
            }

            if(in_array($code, self::OPENPOS_RMA_EXCLUDED_METHODS)) {
                continue;
            }

            if(!isset($this->paymentMethods[$code])) {
                $this->paymentMethods[$code] = [
                    'code' => $code,
                    'title' => $paymentMethod['title'],
                    'amount' => 0
                ];
            }
        }
    }

    /**
     * Save RMA payment method to quote.
     *
     * @return void
     */
    public function save($method): void
    {
        $this->applied = true;

        $payment = $this->checkoutSession->getQuote()->getPayment();
        $payment->setAdditionalInformation('openpos_rma_method_code', $method);
        $payment->setAdditionalInformation('openpos_rma_method_title', $this->paymentMethods[$method]['title']);

        $this->checkoutSession->getQuote()->save();
    }


    /**
     * @param EvaluationResultFactory $factory
     * @return EvaluationResultInterface
     */
    public function evaluateCompletion(EvaluationResultFactory $factory): EvaluationResultInterface
    {
        if(!$this->applied) {
            return $factory->createErrorMessage((string) __('Cannot complete refund. You must select a refund method.'));
        }

        return $factory->createSuccess();
    }
}

<?php

declare(strict_types=1);

namespace Zero1\OpenPosRma\Magewire;

use Magewirephp\Magewire\Component;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Zero1\OpenPos\Model\Configuration as OpenPosConfiguration;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Quote\Api\CartRepositoryInterface;

class Rma extends Component
{
    public $listeners = ['$set', 'addRma'];

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var OpenPosConfiguration
     */
    protected $openPosConfiguration;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var bool
     */
    public $isVisible = false;

    /**
     * @var string
     */
    public $skuInput = '';

    /**
     * @var string
     */
    public $customPriceInput = null;

    /**
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param ProductCollectionFactory $productCollectionFactory
     * @param OpenPosConfiguration $openPosConfiguration
     * @param ObjectFactory $objectFactory
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        ProductCollectionFactory $productCollectionFactory,
        OpenPosConfiguration $openPosConfiguration,
        ObjectFactory $objectFactory,
        CartRepositoryInterface $cartRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->openPosConfiguration = $openPosConfiguration;
        $this->objectFactory = $objectFactory;
        $this->cartRepository = $cartRepository;
    }

    public function addRma()
    {
        try {
            $product = null;
            $product = $this->productRepository->get($this->skuInput);
        } catch(\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Cannot find product by SKU, so use barcode attribute
            $barcodeAttribute = $this->openPosConfiguration->getBarcodeAttribute();
            if($barcodeAttribute) {
                $productCollection = $this->productCollectionFactory->create();
                $productCollection->addAttributeToFilter($barcodeAttribute, ['eq' => $this->skuInput]);
                $productId = $productCollection->getFirstItem()->getId();
                if($productId) {
                    $product = $this->productRepository->getById($productId);
                }
            }

            if(!$product) {
                $this->dispatchErrorMessage('Error: cannot find product matching SKU / barcode / attribute.');
                return;
            }
        }

        if($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            $this->dispatchErrorMessage('Error: the product is not a simple product.');
            return;
        }

        try {
            $item = $this->addProductToQuote($product);
            $this->redirect('/');
        } catch(\Exception $e) {
            $this->dispatchErrorMessage('There was a problem adding this product to the cart.');
            return;
        }
    }

    protected function addProductToQuote($product)
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            $request = $this->objectFactory->create(['qty' => 1]);
            $item = $quote->addProduct($product, $request);

            $price = $product->getPrice();
            $this->customPriceInput = abs((float)$this->customPriceInput);
            if($this->customPriceInput != 0) {
                $price = $this->customPriceInput;
            }

            $item->setCustomPrice(-$price);
            $item->setOriginalCustomPrice(-$price);
            $item->getProduct()->setIsSuperMode(true);

            $quote->setTotalsCollectedFlag(false);
            $this->cartRepository->save($quote);

            $quote->collectTotals();
            $this->cartRepository->save($quote);

            return $item;
        } catch(\Exception $e) {
            $this->dispatchErrorMessage('There was a problem adding this product to the cart.');
        }
    }
}

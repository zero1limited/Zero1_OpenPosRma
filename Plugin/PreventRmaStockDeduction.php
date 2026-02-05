<?php
declare(strict_types=1);

namespace Zero1\OpenPosRma\Plugin;

use Zero1\OpenPos\Helper\Data as OpenPosHelper;
use Magento\CatalogInventory\Observer\ProductQty;

class PreventRmaStockDeduction
{
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

    /**
     * Remove any items from QTY deduction logic that have a negative value.
     * Only runs on POS store.
     *
     * @param ProductQty $subject
     * @param array $relatedItems Array of Quote Items
     * @return array
     */
    public function beforeGetProductQty(ProductQty $subject, $relatedItems)
    {
        if(!$this->openPosHelper->currentlyOnPosStore()) {
            return [$relatedItems];
        }

        $filteredItems = [];

        foreach ($relatedItems as $item) {
            // Check this quote item isn't an RMA
            if ($item->getRowTotal() >= 0) {
                $filteredItems[] = $item;
            }
        }

        return [$filteredItems];
    }
}
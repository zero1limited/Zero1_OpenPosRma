<?php
declare(strict_types=1);

namespace Zero1\OpenPosRma\Plugin;

use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\CatalogInventory\Observer\ProductQty;

class PreventRmaStockDeduction
{
    /**
     * @var TillSessionManagement
     */
    protected $tillSessionManagement;

    /**
     * @param TillSessionManagement $tillSessionManagement
     */
    public function __construct(
        TillSessionManagement $tillSessionManagement
    ) {
        $this->tillSessionManagement = $tillSessionManagement;
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
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
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
<?php
declare(strict_types=1);

namespace Zero1\OpenPosRma\Plugin;

use Zero1\OpenPos\Model\TillSessionManagement;
use Magento\Sales\Api\Data\OrderItemInterface;

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
     * @param \Magento\CatalogInventory\Model\StockManagement $subject
     * @param \Magento\Sales\Api\Data\OrderItemInterface[] $items
     * @param int $websiteId
     * @return array
     */
    public function beforeRegisterProductsSale($subject, array $items, $websiteId = null): array
    {
        // Don't modify params if we aren't on POS store.
        if(!$this->tillSessionManagement->currentlyOnPosStore()) {
            return [$items, $websiteId];
        }

        $filteredItems = [];
        foreach ($items as $item) {
            if (!($item instanceof OrderItemInterface)) {
                $filteredItems[] = $item;
                continue;
            }

            $rowTotal = $item->getRowTotal() ?? ($item->getQtyOrdered() * $item->getPrice());

            if ($rowTotal < 0) {
                continue;
            }

            $filteredItems[] = $item;
        }

        return [$filteredItems, $websiteId];
    }
}
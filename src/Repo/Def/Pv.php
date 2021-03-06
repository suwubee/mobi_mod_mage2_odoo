<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Odoo\Repo\Def;

use Praxigento\Odoo\Repo\IPv;
use Praxigento\Pv\Data\Entity\Product as EntityPvProduct;
use Praxigento\Pv\Data\Entity\Stock\Item as EntityPvStockItem;

class Pv implements IPv
{
    /** @var  \\Praxigento\Pv\Repo\Entity\Product */
    protected $_repoPvProduct;
    /** @var \Praxigento\Pv\Repo\Entity\Stock\Item */
    protected $_repoPvStockItem;

    public function __construct(
        \Praxigento\Pv\Repo\Entity\Product $repoPvProduct,
        \Praxigento\Pv\Repo\Entity\Stock\Item $repoPvStockItem
    ) {
        $this->_repoPvProduct = $repoPvProduct;
        $this->_repoPvStockItem = $repoPvStockItem;
    }

    public function getWarehousePv($stockItemMageId)
    {
        $result = null;
        $data = $this->_repoPvStockItem->getById($stockItemMageId);
        if ($data) {
            $result = $data->getPv();
        }
        return $result;
    }

    public function registerProductWholesalePv($productMageId, $pv)
    {
        $bind = [
            EntityPvProduct::ATTR_PROD_REF => $productMageId,
            EntityPvProduct::ATTR_PV => $pv
        ];
        $this->_repoPvProduct->create($bind);
    }

    public function registerWarehousePv($stockItemMageId, $pv)
    {
        $bind = [
            EntityPvStockItem::ATTR_STOCK_ITEM_REF => $stockItemMageId,
            EntityPvStockItem::ATTR_PV => $pv
        ];
        $this->_repoPvStockItem->create($bind);
    }

    public function updateProductWholesalePv($productMageId, $pv)
    {
        $bind = [
            EntityPvProduct::ATTR_PROD_REF => $productMageId,
            EntityPvProduct::ATTR_PV => $pv
        ];
        $where = EntityPvProduct::ATTR_PROD_REF . '=' . (int)$productMageId;
        $this->_repoPvProduct->update($bind, $where);
    }

    public function updateWarehousePv($stockItemMageId, $pv)
    {
        $bind = [
            EntityPvStockItem::ATTR_STOCK_ITEM_REF => $stockItemMageId,
            EntityPvStockItem::ATTR_PV => $pv
        ];
        $where = EntityPvStockItem::ATTR_STOCK_ITEM_REF . '=' . (int)$stockItemMageId;
        $this->_repoPvStockItem->update($bind, $where);
    }
}
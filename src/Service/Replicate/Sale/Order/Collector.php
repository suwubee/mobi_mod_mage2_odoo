<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Odoo\Service\Replicate\Sale\Order;

use Praxigento\Odoo\Config as Cfg;

/**
 * Collect order data and compose Odoo compatible data object.
 */
class Collector
{
    /** @var  \Praxigento\Odoo\Tool\IBusinessCodesManager */
    protected $manBusinessCodes;
    /** @var  \Praxigento\Core\Tool\IFormat */
    protected $manFormat;
    /** @var \Praxigento\Warehouse\Tool\IStockManager */
    protected $manStock;
    /** @var \Praxigento\Odoo\Repo\Query\Replicate\Sale\Orders\Items\Lots\Get\Builder */
    protected $qbLots;
    /** @var \Praxigento\Odoo\Repo\Query\Replicate\Sale\Orders\Tax\Item\Get\Builder */
    protected $qbTaxItems;
    /** @var \Magento\Customer\Api\CustomerRepositoryInterface */
    protected $repoCustomer;
    /** @var \Praxigento\Downline\Repo\Entity\Customer */
    protected $repoDwnlCustomer;
    /** @var \Praxigento\Core\Repo\IGeneric */
    protected $repoGeneric;
    /** @var \Praxigento\Odoo\Repo\Entity\Product */
    protected $repoOdooProd;
    /** @var \Praxigento\Pv\Repo\Entity\Sale */
    protected $repoPvSale;
    /** @var \Praxigento\Pv\Repo\Entity\Sale\Item */
    protected $repoPvSaleItem;
    /** @var \Praxigento\Odoo\Repo\Entity\Warehouse */
    protected $repoWarehouse;

    public function __construct(
        \Praxigento\Warehouse\Tool\IStockManager $manStock,
        \Praxigento\Odoo\Tool\IBusinessCodesManager $manBusinessCodes,
        \Praxigento\Core\Tool\IFormat $manFormat,
        \Praxigento\Core\Repo\IGeneric $repoGeneric,
        \Magento\Customer\Api\CustomerRepositoryInterface $repoCustomer,
        \Praxigento\Downline\Repo\Entity\Customer $repoDwnlCustomer,
        \Praxigento\Pv\Repo\Entity\Sale $repoPvSale,
        \Praxigento\Pv\Repo\Entity\Sale\Item $repoPvSaleItem,
        \Praxigento\Odoo\Repo\Entity\Warehouse $repoWarehouse,
        \Praxigento\Odoo\Repo\Entity\Product $repoOdooProd,
        \Praxigento\Odoo\Repo\Query\Replicate\Sale\Orders\Items\Lots\Get\Builder $qbLots,
        \Praxigento\Odoo\Repo\Query\Replicate\Sale\Orders\Tax\Item\Get\Builder $qbTaxItems
    )
    {
        $this->manStock = $manStock;
        $this->manBusinessCodes = $manBusinessCodes;
        $this->manFormat = $manFormat;
        $this->repoGeneric = $repoGeneric;
        $this->repoCustomer = $repoCustomer;
        $this->repoDwnlCustomer = $repoDwnlCustomer;
        $this->repoPvSale = $repoPvSale;
        $this->repoPvSaleItem = $repoPvSaleItem;
        $this->repoWarehouse = $repoWarehouse;
        $this->repoOdooProd = $repoOdooProd;
        $this->qbLots = $qbLots;
        $this->qbTaxItems = $qbTaxItems;
    }

    /**
     * Get total PV for Sale Order Item.
     *
     * @param int $itemId Sale Order Item ID
     * @return float Total PV for the Sale Order Item
     */
    protected function dbGetItemPvTotal($itemId)
    {
        /** @var \Praxigento\Pv\Data\Entity\Sale\Item $data */
        $data = $this->repoPvSaleItem->getById($itemId);
        $result = $data->getTotal();
        return $result;
    }

    /**
     * Get all taxes for the Sale Item by item ID (Magento ID).
     *
     * @param int $itemId
     * @return \Flancer32\Lib\Data[]
     */
    protected function dbGetItemTaxes($itemId)
    {
        $result = [];
        $entity = Cfg::ENTITY_MAGE_SALES_ORDER_TAX_ITEM;
        $where = Cfg::E_SALE_ORDER_TAX_ITEM_A_ITEM_ID . '=' . (int)$itemId;
        $rows = $this->repoGeneric->getEntities($entity, '*', $where);
        foreach ($rows as $row) {
            $data = new \Flancer32\Lib\Data($row);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Get magento data for lots related to order item to be converted into Odoo format.
     *
     * @param $itemId
     * @return \Flancer32\Lib\Data[]
     */
    protected function dbGetLots($itemId)
    {
        $result = [];
        $query = $this->qbLots->build();
        $conn = $query->getConnection();
        $bind = [
            $this->qbLots::BIND_SALE_ITEM_ID => $itemId
        ];
        $rows = $conn->fetchAll($query, $bind);
        foreach ($rows as $row) {
            $data = new \Flancer32\Lib\Data($row);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Get all taxes rates for sale order by order ID.
     *
     * @param $saleId
     * @return \Flancer32\Lib\Data[]
     */
    protected function dbGetOrderTaxes($saleId)
    {
        $result = [];
        $entity = Cfg::ENTITY_MAGE_SALES_ORDER_TAX;
        $where = Cfg::E_SALE_ORDER_TAX_A_ORDER_ID . '=' . (int)$saleId;
        $rows = $this->repoGeneric->getEntities($entity, null, $where);
        foreach ($rows as $row) {
            $data = new \Flancer32\Lib\Data($row);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Get tax rates for shipping by sale order ID.
     *
     * @param int $saleId
     * @return \Flancer32\Lib\Data[]
     */
    protected function dbGetShippingTaxes($saleId)
    {
        $result = [];
        /* get base query */
        $query = $this->qbTaxItems->build();
        /* add conditions */
        $bindType = 'taxType';
        $cond = $this->qbTaxItems::AS_ITEM_TAX . '.' . Cfg::E_SALE_ORDER_TAX_ITEM_A_TAXABLE_ITEM_TYPE
            . '=:' . $bindType;
        $query->where($cond);
        /* map query parameters */
        $bind = [
            $this->qbTaxItems::BIND_ORDER_ID => $saleId,
            $bindType => \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::ITEM_TYPE_SHIPPING
        ];
        /* execute query */
        $conn = $query->getConnection();
        $rows = $conn->fetchAll($query, $bind);
        foreach ($rows as $row) {
            $data = new \Flancer32\Lib\Data($row);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Get tax rate code by tax rate ID.
     *
     * @param int $taxId
     * @return string|null
     */
    protected function dbGetTaxCodeByTaxId($taxId)
    {
        $result = null;
        $entity = Cfg::ENTITY_MAGE_SALES_ORDER_TAX;
        $where = Cfg::E_SALE_ORDER_TAX_A_TAX_ID . '=' . (int)$taxId;
        $rows = $this->repoGeneric->getEntities($entity, '*', $where);
        if (is_array($rows)) {
            /* one only row should present in result set */
            $row = reset($rows);
            $result = $row[Cfg::E_SALE_ORDER_TAX_A_CODE];
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\Contact
     */
    protected function getAddressBilling(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $addrMage */
        $addrMage = $sale->getBillingAddress();
        $result = $this->getContact($addrMage);
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\Contact
     */
    protected function getAddressShipping(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        /** @var \Magento\Sales\Api\Data\OrderAddressInterface $addrMage */
        $addrMage = $sale->getShippingAddress();
        $result = $this->getContact($addrMage);
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $addr
     * @return \Praxigento\Odoo\Data\Odoo\Contact
     */
    protected function getContact(\Magento\Sales\Api\Data\OrderAddressInterface $addr)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\Contact();
        /* collect data */
        $name = $addr->getName();
        $phone = $addr->getTelephone();
        $email = $addr->getEmail();
        $country = $addr->getCountryId();
        $state = $addr->getRegionCode();
        $city = $addr->getCity();
        $street = $addr->getStreet(); // street data is array
        $street = implode('', $street);
        $zip = $addr->getPostcode();
        /* init Odoo data object */
        if ($name) $result->setName($name);
        if ($phone) $result->setPhone($phone);
        if ($email) $result->setEmail($email);
        if ($country) $result->setCountry($country);
        if ($state) $result->setState($state);
        if ($city) $result->setCity($city);
        if ($street) $result->setStreet($street);
        if ($zip) $result->setZip($zip);
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Line\Tax
     */
    protected function getItemTax(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Line\Tax();
        $itemMageId = $item->getItemId();
        /* collect data */
        $rates = $this->getItemTaxRates($itemMageId);
        $rate = reset($rates);
        if ($rate) {
            $base = $rate->getAmount() / $rate->getPercent();
            $base = $this->manFormat->toNumber($base);
            /* populate Odoo Data Object */
            $result->setBase($base);
            $result->setRates($rates);
        }
        return $result;
    }

    /**
     * @param int $itemId
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Tax\Rate[]
     */
    protected function getItemTaxRates($itemId)
    {
        $result = [];
        $rates = $this->dbGetItemTaxes($itemId);
        foreach ($rates as $rate) {
            /* collect data */
            $taxId = $rate->get(Cfg::E_SALE_ORDER_TAX_ITEM_A_TAX_ID);
            $code = $this->dbGetTaxCodeByTaxId($taxId);
            $percent = $rate->get(Cfg::E_SALE_ORDER_TAX_ITEM_A_TAX_PERCENT);
            $percent /= 100;
            $percent = $this->manFormat->toNumber($percent, Cfg::ODOO_API_PERCENT_ROUND);
            $amount = $rate->get(Cfg::E_SALE_ORDER_TAX_ITEM_A_REAL_BASE_AMOUNT);
            $amount = $this->manFormat->toNumber($amount);
            /* init Odoo data object */
            $data = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Tax\Rate();
            $data->setCode($code);
            $data->setAmount($amount);
            $data->setPercent($percent);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Extract Odoo compatible customer data from Magento order.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Customer
     */
    protected function getOrderCustomer(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Customer();
        /* collect data */
        $custMageId = (int)$sale->getCustomerId();
        $dwnlCust = $this->repoDwnlCustomer->getById($custMageId);
        $ref = $dwnlCust->getHumanRef();
        $name = $sale->getCustomerName();
        $mageCust = $this->repoCustomer->getById($custMageId);
        $groupId = $mageCust->getGroupId();
        $groupCode = $this->manBusinessCodes->getBusCodeForCustomerGroupById($groupId);
        /* init Odoo data object */
        $result->setIdMage($custMageId);
        $result->setIdMlm($ref);
        $result->setName($name);
        $result->setGroupCode($groupCode);
        return $result;
    }

    /**
     * Extract data from magento model and compose initial Odoo data object,
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Line
     */
    protected function getOrderLine(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Line();
        /* collect data */
        $itemIdMage = $item->getId();
        $productIdMage = $item->getProductId();
        $productIdOdoo = (int)$this->repoOdooProd->getOdooIdByMageId($productIdMage);
        $qty = $item->getQtyOrdered();
        $qty = $this->manFormat->toNumber($qty);
        $lots = $this->getOrderLineLots($itemIdMage);
        $tax = $this->getItemTax($item);
        /* PV attributes */
        $pv = $this->dbGetItemPvTotal($itemIdMage);
        $pv = $this->manFormat->toNumber($pv);
        /* init Odoo data object */
        $result->setProductIdOdoo($productIdOdoo);
        $result->setQty($qty);
        $result->setLots($lots);
        $result->setTax($tax);
        $result->setPv($pv);
        return $result;
    }

    /**
     * Get lots data from DB and convert into Odoo API data.
     *
     * @param int $itemId
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Line\Lot[]
     */
    protected function getOrderLineLots($itemId)
    {
        $result = [];
        /* request lots data for the sale item */
        $dbDataLots = $this->dbGetLots($itemId);
        foreach ($dbDataLots as $one) {
            $lot = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Line\Lot();
            /* Lot's ID in Odoo */
            $idOdoo = (int)$one->get($this->qbLots::A_ODOO_ID);
            /* skip lot for products w/o lots */
            if ($idOdoo != Cfg::NULL_LOT_ID) $lot->setIdOdoo($idOdoo);
            /* qty in this lot */
            $qty = $one->get($this->qbLots::A_TOTAL);
            $qty = $this->manFormat->toNumber($qty);
            $lot->setQty($qty);
            $result[] = $lot;
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Line[]
     */
    protected function getOrderLines(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = [];
        /* collect data */
        $items = $sale->getAllItems();
        foreach ($items as $item) {
            /* process order item */
            $line = $this->getOrderLine($item);
            $result[] = $line;
        }
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\Payment[]
     */
    protected function getOrderPayments(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = [];
        $odooPayment = new \Praxigento\Odoo\Data\Odoo\Payment();
        /* collect data */
        $magePayment = $sale->getPayment();
        $code = $this->manBusinessCodes->getBusCodeForPaymentMethod($magePayment);
        $ordered = $magePayment->getBaseAmountOrdered();
        $amount = $this->manFormat->toNumber($ordered);
        /* populate Odoo Data Object */
        $odooPayment->setCode($code);
        $odooPayment->setAmount($amount);
        $result[] = $odooPayment;
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Price
     */
    protected function getOrderPrice(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Price();
        /* collect data */
        $currency = $sale->getBaseCurrencyCode();;
        $paid = $sale->getBaseTotalPaid();
        $due = $sale->getBaseTotalDue();
        $paid += $due;
        $paid = $this->manFormat->toNumber($paid);
        $tax = $this->getOrderPriceTax($sale);
        /* populate Odoo Data Object */
        $result->setCurrency($currency);
        $result->setPaid($paid);
        $result->setTax($tax);
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Price\Tax
     */
    protected function getOrderPriceTax(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Price\Tax();
        /* collect data */
        $total = $sale->getBaseTaxAmount();
        $total = $this->manFormat->toNumber($total);
        $base = $sale->getBaseGrandTotal() - $total;
        $base = $this->manFormat->toNumber($base);
        /* populate Odoo Data Object */
        $result->setBase($base);
        $result->setTotal($total);
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Tax\Rate[]
     */
    protected function getOrderPriceTaxRates(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = [];
        $saleId = $sale->getId();
        $rows = $this->dbGetOrderTaxes($saleId);
        foreach ($rows as $row) {
            /* collect data */
            $code = $row->get(Cfg::E_SALE_ORDER_TAX_A_CODE);
            $percent = $row->get(Cfg::E_SALE_ORDER_TAX_A_PERCENT);
            $percent = $this->manFormat->toNumber($percent, Cfg::ODOO_API_PERCENT_ROUND);
            $amount = $row->get(Cfg::E_SALE_ORDER_TAX_A_AMOUNT);
            $amount = $this->manFormat->toNumber($amount);
            /* populate Odoo Data Object */
            $rate = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Tax\Rate();
            $rate->setCode($code);
            $rate->setPercent($percent);
            $rate->setAmount($amount);
            $result[] = $rate;
        }
        return $result;
    }

    /**
     * Convert Magento Order to Odoo API data.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder
     */
    public function getSaleOrder(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder();

        /* Collect order data */
        // id_mage
        $orderIdMage = (int)$sale->getId();
        // warehouse_id_odoo
        $warehouseIdOdoo = (int)$this->getWarehouseIdOdoo($sale);
        // number
        $number = $sale->getIncrementId();
        // date (will be below)
        // customer
        $customer = $this->getOrderCustomer($sale);
        // addr_billing
        $addrBilling = $this->getAddressBilling($sale);
        // addr_shipping
        $addrShipping = $this->getAddressShipping($sale);
        // pv_total (with date paid)
        $pvOrder = $this->repoPvSale->getById($orderIdMage);
        $pvTotal = $this->manFormat->toNumber($pvOrder->getTotal());
        $datePaid = $pvOrder->getDatePaid();
        // price
        $price = $this->getOrderPrice($sale);
        // lines
        $lines = $this->getOrderLines($sale);
        // shipping
        $shipping = $this->getShipping($sale);
        // payments
        $payments = $this->getOrderPayments($sale);
        /* populate Odoo Data Object */
        $result->setIdMage($orderIdMage);
        $result->setWarehouseIdOdoo($warehouseIdOdoo);
        $result->setNumber($number);
        $result->setDatePaid($datePaid);
        $result->setCustomer($customer);
        $result->setAddrBilling($addrBilling);
        $result->setAddrShipping($addrShipping);
        $result->setPvTotal($pvTotal);
        $result->setPrice($price);
        $result->setLines($lines);
        $result->setShipping($shipping);
        $result->setPayments($payments);

        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Shipping
     */
    protected function getShipping(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Shipping();
        /* collect data */
        $code = $this->manBusinessCodes->getBusCodeForShippingMethod($sale);
        $tax = $this->getShippingTax($sale);
        /* populate Odoo Data Object */
        $result->setCode($code);
        $result->setTax($tax);
        return $result;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Shipping\Tax
     */
    protected function getShippingTax(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $result = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Shipping\Tax();
        /* collect data */
        $base = 0;
        $saleId = $sale->getEntityId();
        $rates = $this->getShippingTaxRates($saleId);
        /* calc base for tax incl. scheme */
        $rate = reset($rates);
        if ($rate) {
            $percent = $rate->getPercent();
            $amount = $rate->getAmount();
            if ($percent > Cfg::DEF_ZERO) {
                $base = $amount / $percent;
                $base = $this->manFormat->toNumber($base);
            }
        }
        /* populate Odoo Data Object */
        $result->setBase($base);
        $result->setRates($rates);
        return $result;
    }

    /**
     * @param int $saleId
     * @return \Praxigento\Odoo\Data\Odoo\SaleOrder\Tax\Rate[]
     */
    protected function getShippingTaxRates($saleId)
    {
        $result = [];
        $rows = $this->dbGetShippingTaxes($saleId);
        foreach ($rows as $row) {
            /* collect data */
            $code = $row->get($this->qbTaxItems::A_TAX_CODE);
            $percent = $row->get($this->qbTaxItems::A_TAX_PERCENT);
            $percent /= 100;
            $percent = $this->manFormat->toNumber($percent, Cfg::ODOO_API_PERCENT_ROUND);
            $amount = $row->get($this->qbTaxItems::A_AMOUNT);
            $amount = $this->manFormat->toNumber($amount);

            /* populate Odoo Data Object */
            $data = new \Praxigento\Odoo\Data\Odoo\SaleOrder\Tax\Rate();
            $data->setCode($code);
            $data->setPercent($percent);
            $data->setAmount($amount);
            $result[] = $data;
        }
        return $result;
    }

    /**
     * Convert Magento's $storeId to Odoo's $warehouseId.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $sale
     * @return int
     */
    protected function getWarehouseIdOdoo(\Magento\Sales\Api\Data\OrderInterface $sale)
    {
        $storeId = $sale->getStoreId();
        $stockId = $this->manStock->getStockIdByStoreId($storeId);
        $warehouse = $this->repoWarehouse->getById($stockId);
        $result = $warehouse->getOdooRef();
        return $result;
    }
}
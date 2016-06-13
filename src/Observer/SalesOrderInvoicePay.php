<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Replicate paid order to Odoo.
 */
class SalesOrderInvoicePay implements ObserverInterface
{


    public function __construct()
    {

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getData(self::DATA_INVOICE);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $invoice->getOrder();
        throw new \Exception('don\'t pay to the invoice');
        return;
    }

    /**
     * Define current stock/warehouse for the customer.
     *
     * @param $custId
     * @return int
     */
    private function getStockIdByCustomer($custId)
    {
        /* TODO: move stock id to the service */
        /* get stock ID for the customer */
        $reqStock = new \Praxigento\Warehouse\Service\Customer\Request\GetCurrentStock();
        $reqStock->setCustomerId($custId);
        $respStock = $this->_callCustomer->getCurrentStock($reqStock);
        $result = $respStock->getStockId();
        return $result;
    }
}
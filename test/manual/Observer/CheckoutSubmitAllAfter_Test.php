<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Observer;

use Magento\Framework\App\ObjectManager;

include_once(__DIR__ . '/../phpunit_bootstrap.php');

class CheckoutSubmitAllAfter_ManualTest extends \Praxigento\Core\Test\BaseIntegrationTest
{
    /** @var  CheckoutSubmitAllAfter */
    private $obj;
    /** @var  \Magento\Sales\Api\OrderRepositoryInterface */
    private $repoSaleOrder;

    protected function setUp()
    {
        $this->repoSaleOrder = $this->_manObj->create(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->obj = $this->_manObj->create(CheckoutSubmitAllAfter::class);
    }


    public function test_execute()
    {
        $event = new \Magento\Framework\Event\Observer();
        /* load Magento order */
        $mageOrder = $this->repoSaleOrder->get(1);
        $event->setOrder($mageOrder);
        $resp = $this->obj->execute($event);
        $this->assertNotNull($resp);
    }
}
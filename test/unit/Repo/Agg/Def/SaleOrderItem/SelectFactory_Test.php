<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Repo\Agg\Store\SaleOrderItem;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class SelectFactory_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo
{
    /** @var  SelectFactory */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new SelectFactory(
            $this->mResource
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(SelectFactory::class, $this->obj);
    }

    public function test_getQueryToSelect()
    {
        /** === Setup Mocks === */
        // $result = $this->_conn->select();
        $mResult = $this->_mockDbSelect(['from', 'joinLeft', 'where']);
        $this->mConn
            ->shouldReceive('select')->once()
            ->andReturn($mResult);
        // ... $this->_resource->getTableName(...)
        $this->mResource
            ->shouldReceive('getTableName');
        /** === Call and asserts  === */
        $res = $this->obj->getQueryToSelect();
        $this->assertTrue($res instanceof \Magento\Framework\DB\Select);
    }

    /**
     * @expectedException \Exception
     */
    public function test_getQueryToSelectCount()
    {
        /** === Call and asserts  === */
        $this->obj->getQueryToSelectCount();
    }

}
<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Plugin\Framework\View\Element\UiComponent\DataProvider\Sub;

include_once(__DIR__ . '/../../../../../../../phpunit_bootstrap.php');

class QueryModifier_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Mockery
{
    /** @var  \Mockery\MockInterface */
    private $mConn;
    /** @var  \Mockery\MockInterface */
    private $mResource;
    /** @var  QueryModifier */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mConn = $this->_mockConn();
        $this->mResource = $this->_mockResourceConnection($this->mConn);
        /** create object to test */
        $this->obj = new QueryModifier(
            $this->mResource
        );
    }

    public function test_addFieldsMapping()
    {
        /** === Test Data === */
        $mCollection = $this->_mock(\Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class);
        /** === Setup Mocks === */
        // $collection->addFilterToMap($fieldAlias, $fieldFullName);
        $mCollection->shouldReceive('addFilterToMap')->once();
        /** === Call and asserts  === */
        $this->obj->addFieldsMapping($mCollection);
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(QueryModifier::class, $this->obj);
    }

    public function test_populateSelect()
    {
        /** === Test Data === */
        $TBL_SALE_ORDER = 'sale order table';
        $mCollection = $this->_mock(\Magento\Sales\Model\ResourceModel\Order\Grid\Collection::class);
        /** === Setup Mocks === */
        // $select = $collection->getSelect();
        $mSelect = $this->_mockDbSelect(['joinLeft']);
        $mCollection
            ->shouldReceive('getSelect')->once()
            ->andReturn($mSelect);
        // $tbl = [self::AS_TBL_ODOO_SALE => $this->_resource->getTableName(SaleOrder::ENTITY_NAME)];
        $this->mResource
            ->shouldReceive('getTableName')->once()
            ->with(\Praxigento\Odoo\Data\Entity\SaleOrder::ENTITY_NAME)
            ->andReturn();
        /** === Call and asserts  === */
        $this->obj->populateSelect($mCollection);
    }

}
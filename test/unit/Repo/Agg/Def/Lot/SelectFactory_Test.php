<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Repo\Agg\Store\Lot;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');

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
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')->once()
            ->andReturn($mQuery);
        // $tblWrhs = [$asWrhs => $this->_resource->getTableName(EntityWrhsLot::ENTITY_NAME)];
        // $tblOdoo = [$asOdoo => $this->_resource->getTableName(EntityLot::ENTITY_NAME)];
        $this->mResource
            ->shouldReceive('getTableName')->twice();
        // $result->from($tblWrhs, $cols);
        $mQuery->shouldReceive('from')->once();
        // $result->joinLeft($tblOdoo, $on, $cols);
        $mQuery->shouldReceive('joinLeft')->once();
        /** === Call and asserts  === */
        $res = $this->obj->getQueryToSelect();
        $this->assertTrue($res instanceof \Magento\Framework\DB\Select);
    }

    public function test_getQueryToSelectCount()
    {
        /** === Setup Mocks === */
        // $result = $this->_conn->select();
        $mQuery = $this->_mockDbSelect();
        $this->mConn
            ->shouldReceive('select')->once()
            ->andReturn($mQuery);
        // $tblWrhs = [$asWrhs => $this->_resource->getTableName(EntityWrhsLot::ENTITY_NAME)];
        // $tblOdoo = [$asOdoo => $this->_resource->getTableName(EntityLot::ENTITY_NAME)];
        $this->mResource
            ->shouldReceive('getTableName')->twice();
        // $result->from($tblWrhs, $cols);
        $mQuery->shouldReceive('from')->once();
        // $result->joinLeft($tblOdoo, $on, $cols);
        $mQuery->shouldReceive('joinLeft')->once();
        /** === Call and asserts  === */
        $res = $this->obj->getQueryToSelectCount();
        $this->assertTrue($res instanceof \Magento\Framework\DB\Select);
    }

}
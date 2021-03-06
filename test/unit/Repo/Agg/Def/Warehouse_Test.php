<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Repo\Agg\Def;

use Praxigento\Odoo\Repo\Agg\Data\Warehouse as AggWarehouse;
use Praxigento\Odoo\Repo\Entity\Warehouse as RepoEntityWarehouse;
use Praxigento\Warehouse\Data\Agg\Warehouse as WrhsAggWarehouse;
use Praxigento\Warehouse\Repo\Agg\Def\Warehouse as WrhsRepoAggWarehouse;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Warehouse_UnitTest
    extends \Praxigento\Core\Test\BaseCase\Repo
{
    /** @var  \Mockery\MockInterface */
    private $mFactorySelect;
    /** @var  \Mockery\MockInterface */
    private $mManObj;
    /** @var  \Mockery\MockInterface */
    private $mManTrans;
    /** @var  \Mockery\MockInterface */
    private $mRepoEntityWarehouse;
    /** @var  \Mockery\MockInterface */
    private $mRepoWrhsAggWarehouse;
    /** @var  Warehouse */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /** create mocks */
        $this->mManObj = $this->_mockObjectManager();
        $this->mManTrans = $this->_mockTransactionManager();
        $this->mRepoWrhsAggWarehouse = $this->_mock(WrhsRepoAggWarehouse::class);
        $this->mRepoEntityWarehouse = $this->_mock(RepoEntityWarehouse::class);
        $this->mFactorySelect = $this->_mock(Warehouse\SelectFactory::class);
        /** setup mocks for constructor */
        /** create object to test */
        $this->obj = new Warehouse(
            $this->mManObj,
            $this->mManTrans,
            $this->mResource,
            $this->mRepoWrhsAggWarehouse,
            $this->mRepoEntityWarehouse,
            $this->mFactorySelect
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(Warehouse::class, $this->obj);
    }

    public function test_create()
    {
        /** === Test Data === */
        $MAGE_ID = 32;
        $ODOO_ID = 54;
        $DATA = new AggWarehouse([AggWarehouse::AS_ODOO_ID => $ODOO_ID]);
        /** === Setup Mocks === */
        // $def = $this->_manTrans->begin();
        $mDef = $this->_mockTransactionDefinition();
        $this->mManTrans
            ->shouldReceive('begin')->once()
            ->andReturn($mDef);
        // $result = $this->_repoWrhsAggWarehouse->create($data);
        $mWrhsData = new WrhsAggWarehouse([WrhsAggWarehouse::AS_ID => $MAGE_ID]);
        $this->mRepoWrhsAggWarehouse
            ->shouldReceive('create')->once()
            ->andReturn($mWrhsData);
        // $this->_repoEntityWarehouse->create($bind);
        $this->mRepoEntityWarehouse
            ->shouldReceive('create')->once();
        // $this->_manTrans->commit($def);
        $this->mManTrans
            ->shouldReceive('commit')->once();
        // $result = $this->_manObj->create(AggWarehouse::class);
        $this->mManObj->shouldReceive('create')->once()
            ->andReturn(new AggWarehouse());
        // $this->_manTrans->end($def);
        $this->mManTrans
            ->shouldReceive('end')->once();
        /** === Call and asserts  === */
        $res = $this->obj->create($DATA);

    }

    public function test_getById()
    {
        /** === Test Data === */
        $ID = 32;
        $DATA = [AggWarehouse::AS_ID => $ID];
        /** === Setup Mocks === */
        // $query = $this->_subSelect->getQueryToSelect();
        $mQuery = $this->_mockDbSelect();
        $this->mFactorySelect
            ->shouldReceive('getQueryToSelect')->once()
            ->andReturn($mQuery);
        // $query->where(WrhsRepoAggWarehouse::AS_STOCK . '.' . Cfg::E_CATINV_STOCK_A_STOCK_ID . '=:id');
        $mQuery->shouldReceive('where')->once();
        // $data = $this->_conn->fetchRow($query, ['id' => $id]);
        $this->mConn
            ->shouldReceive('fetchRow')->once()
            ->andReturn($DATA);
        // $result = $this->_manObj->create(AggWarehouse::class);
        $this->mManObj->shouldReceive('create')->once()
            ->andReturn(new AggWarehouse());
        /** === Call and asserts  === */
        $res = $this->obj->getById($ID);
        $this->assertEquals($ID, $res->getId());
    }

    public function test_getByOdooId()
    {
        /** === Test Data === */
        $ODOO_ID = 32;
        $DATA = [AggWarehouse::AS_ODOO_ID => $ODOO_ID];
        /** === Setup Mocks === */
        // $query = $this->_subSelect->getQueryToSelect();
        $mQuery = $this->_mockDbSelect();
        $this->mFactorySelect
            ->shouldReceive('getQueryToSelect')->once()
            ->andReturn($mQuery);
        // $query->where(static::AS_ODOO . '.' . EntityWarehouse::ATTR_ODOO_REF . '=:id');
        $mQuery->shouldReceive('where')->once();
        // $data = $this->_conn->fetchRow($query, ['id' => $odooId]);
        $this->mConn
            ->shouldReceive('fetchRow')->once()
            ->andReturn($DATA);
        // $result = $this->_manObj->create(AggWarehouse::class);
        $this->mManObj->shouldReceive('create')->once()
            ->andReturn(new AggWarehouse());
        /** === Call and asserts  === */
        $res = $this->obj->getByOdooId($ODOO_ID);
        $this->assertEquals($ODOO_ID, $res->getOdooId());
    }

    public function test_getQueryToSelect()
    {
        /** === Setup Mocks === */
        // $result = $this->_factorySelect->getQueryToSelect();
        $mQuery = $this->_mockDbSelect();
        $this->mFactorySelect
            ->shouldReceive('getQueryToSelect')->once()
            ->andReturn($mQuery);
        /** === Call and asserts  === */
        $res = $this->obj->getQueryToSelect();
        $this->assertInstanceOf(\Magento\Framework\DB\Select::class, $res);
    }

    public function test_getQueryToSelectCount()
    {
        /** === Setup Mocks === */
        // $result = $this->_factorySelect->getQueryToSelectCount();
        $mQuery = $this->_mockDbSelect();
        $this->mFactorySelect
            ->shouldReceive('getQueryToSelectCount')->once()
            ->andReturn($mQuery);
        /** === Call and asserts  === */
        $res = $this->obj->getQueryToSelectCount();
        $this->assertInstanceOf(\Magento\Framework\DB\Select::class, $res);
    }
}
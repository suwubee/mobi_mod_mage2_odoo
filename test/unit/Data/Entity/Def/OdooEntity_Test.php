<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Data\Entity\Def;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class OdooEntityChild extends OdooEntity
{
}

class OdooEntity_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery
{
    /** @var  OdooEntityChild */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new OdooEntityChild();
    }

    public function test_accessors()
    {
        /** === Test Data === */
        $DATE_REPLICATED = '2016-08-18';
        $MAGE_ID = 21;
        $ODOO_ID = 43;
        /** === Call and asserts  === */
        $this->obj->setDateReplicated($DATE_REPLICATED);
        $this->obj->setMageRef($MAGE_ID);
        $this->obj->setOdooRef($ODOO_ID);
        $this->assertEquals($DATE_REPLICATED, $this->obj->getDateReplicated());
        $this->assertEquals($MAGE_ID, $this->obj->getMageRef());
        $this->assertEquals($ODOO_ID, $this->obj->getOdooRef());
    }

}
<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Data\Api\Bundle\Def;

include_once(__DIR__ . '/../../../../phpunit_bootstrap.php');


class Lot_UnitTest extends \Praxigento\Core\Test\BaseMockeryCase
{
    /** @var  Lot */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /* create object to test */
        $this->obj = new Lot();
    }

    public function test_accessors()
    {
        /* === Test Data === */
        $CODE = 'code';
        $EXP_DATE = 'exp date';
        $ID = 'id';
        /* === Call and asserts  === */
        $this->obj->setCode($CODE);
        $this->obj->setExpirationDate($EXP_DATE);
        $this->obj->setId($ID);
        $this->assertEquals($CODE, $this->obj->getCode());
        $this->assertEquals($EXP_DATE, $this->obj->getExpirationDate());
        $this->assertEquals($ID, $this->obj->getId());
    }

}
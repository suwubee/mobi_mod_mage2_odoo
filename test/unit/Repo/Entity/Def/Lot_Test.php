<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Odoo\Repo\Entity;

include_once(__DIR__ . '/../../../phpunit_bootstrap.php');

class Lot_UnitTest extends \Praxigento\Core\Test\BaseCase\Repo\Entity
{

    /** @var  Lot */
    private $obj;

    protected function setUp()
    {
        parent::setUp();
        /** create object to test */
        $this->obj = new Lot(
            $this->mResource,
            $this->mRepoGeneric
        );
    }

    public function test_constructor()
    {
        /** === Call and asserts  === */
        $this->assertInstanceOf(Lot::class, $this->obj);
    }

}
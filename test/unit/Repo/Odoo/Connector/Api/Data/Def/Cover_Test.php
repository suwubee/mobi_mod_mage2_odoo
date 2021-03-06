<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Repo\Odoo\Connector\Api\Data\Def;

include_once(__DIR__ . '/../../../../../../phpunit_bootstrap.php');

class Cover_UnitTest extends \Praxigento\Core\Test\BaseCase\Mockery
{

    public function test_accessors()
    {
        $RESULT = 'result data';
        $DATA = ['result' => $RESULT];
        $obj = new Cover($DATA);
        $this->assertEquals($RESULT, $obj->getResultData());
    }

}
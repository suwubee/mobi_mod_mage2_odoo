<?php
/**
 * Empty class to get stub for tests
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Setup;

use Praxigento\Core\Lib\Context;

include_once(__DIR__ . '/../phpunit_bootstrap.php');

class InstallData_UnitTest extends \Praxigento\Core\Lib\Test\BaseTestCase {

    public function test_constructor() {
        $obj = new InstallData();
        $this->assertInstanceOf(\Praxigento\Odoo\Setup\InstallData::class, $obj);
    }

}
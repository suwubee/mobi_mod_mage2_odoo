<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */

namespace Praxigento\Odoo\Data\Odoo\SaleOrder\Price;

/**
 * @method float getBase() Tax base amount for whole order (net, amount w/o tax)
 * @method void setBase(float $data) Tax base amount for whole order (net, amount w/o tax)
 * @method float getTotal() Sum of all taxes to be applied to the order.
 * @method void setTotal(float $data) Sum of all taxes to be applied to the order.
 */
class Tax
    extends \Flancer32\Lib\Data
{

}
<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Service;


interface IReplicate
{
    /**
     * @param Replicate\Request\OrderSave $req
     * @return Replicate\Response\OrderSave
     *
     * @deprecated use \Praxigento\Odoo\Service\Replicate\Sale\IOrder
     */
    public function orderSave(Replicate\Request\OrderSave $req);

    /**
     * Save products bundle ('push' replication).
     *
     * @param Replicate\Request\ProductSave $req
     * @return Replicate\Response\ProductSave
     */
    public function productSave(Replicate\Request\ProductSave $req);

    /**
     * Get products bundle from Odoo and save it ('pull' replication).
     *
     * @param Replicate\Request\ProductsFromOdoo $req
     * @return Replicate\Response\ProductsFromOdoo
     */
    public function productsFromOdoo(Replicate\Request\ProductsFromOdoo $req);

}
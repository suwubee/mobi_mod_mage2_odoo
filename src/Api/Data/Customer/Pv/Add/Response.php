<?php
/**
 * User: Alex Gusev <alex@flancer64.com>
 */
namespace Praxigento\Odoo\Api\Data\Customer\Pv\Add;

class Response
    extends \Praxigento\Core\Api\Response
{
    const CODE_CUSTOMER_IS_NOT_FOUND = 'CUSTOMER_IS_NOT_FOUND';
    const CODE_DUPLICATED = 'DUPLICATED';

    /**
     * @return \Praxigento\Odoo\Api\Data\Customer\Pv\Add\Response\Data|null
     */
    public function getData()
    {
        $result = parent::get(self::ATTR_DATA);
        return $result;
    }

    /**
     * @param \Praxigento\Odoo\Api\Data\Customer\Pv\Add\Response\Data $data
     */
    public function setData($data)
    {
        parent::set(self::ATTR_DATA, $data);
    }

}
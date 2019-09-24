<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class LocalPayment
 */
class LocalPayment extends AbstractDb
{
    const TABLE_NAME = 'braintree_lpm';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}

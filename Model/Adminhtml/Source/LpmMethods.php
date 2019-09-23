<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LpmMethods
 */
class LpmMethods implements OptionSourceInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'bancontact', 'label' => __('Bancontact')],
            ['value' => 'eps', 'label' => __('EPS')],
            ['value' => 'giropay', 'label' => __('giropay')],
            ['value' => 'ideal', 'label' => __('iDeal')],
            ['value' => 'sofort', 'label' => __('Klarna Pay Now / SOFORT')],
            ['value' => 'mybank', 'label' => __('MyBank')],
            ['value' => 'p24', 'label' => __('P24')],
            ['value' => 'sepa', 'label' => __('SEPA/ELV Direct Debit')]
        ];
    }
}

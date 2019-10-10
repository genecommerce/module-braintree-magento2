<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class KountEnvironment
 */
class KountEnvironment implements OptionSourceInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'sandbox', 'label' => 'Sandbox'],
            ['value' => 'production', 'label' => 'Production']
        ];
    }
}

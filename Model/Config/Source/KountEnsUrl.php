<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Config\Source;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class KountEnsUrl
 */
class KountEnsUrl extends Field
{
    /**
     * {@inheritDoc}
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        return 'foobar';
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Braintree\Plugin;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Checkout\CustomerData\AbstractItem;
use Magento\Downloadable\Model\Product\Type as Downloadable;
use Magento\Quote\Model\Quote\Item;

/**
 * A plugin class to add the 'is_virtual' property to [virtual, giftcard, downloadable] product
 *
 * Class AddFlagForVirtualProducts
 */
class AddFlagForVirtualProducts
{
    const PRODUCT_TYPE_GIFTCARD = 'giftcard';

    /**
     * Add virtual product flag
     *
     * @param AbstractItem $subject
     * @param array $result
     * @param Item $item
     * @return array
     */
    public function afterGetItemData(AbstractItem $subject, array $result, Item $item): array
    {
        if ($item->getProductType() === ProductType::TYPE_VIRTUAL
            || $item->getProductType() === Downloadable::TYPE_DOWNLOADABLE
            || ($item->getProductType() === self::PRODUCT_TYPE_GIFTCARD && $item->getIsVirtual())) {
            $result['is_virtual'] = 1;
        }
        return $result;
    }
}

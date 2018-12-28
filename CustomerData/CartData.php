<?php
/**
 * HiConversion
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * [http://opensource.org/licenses/MIT]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @Copyright © 2015 HiConversion, Inc. All rights reserved.
 * @license [http://opensource.org/licenses/MIT] MIT License
 */

namespace Magento\Braintree\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * CartData section source
 *
 * @author HiConversion <support@hiconversion.com>
 */
class CartData implements SectionSourceInterface
{

    /**
     * @var \Magento\Braintree\Helper\HicHelper
     */
    private $helper;
   
    /**
     * \Magento\Braintree\Helper\HicHelper $helper
     */
    public function __construct(
        \Magento\Braintree\Helper\HicHelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $data = [];
        if ($this->helper->isEnabled()) {
            $cart = $this->getCartData();
            if (null !== $cart) {
                $data = $cart;
            }
        } else {
            $data["disabled"] = true;
        }
        return $data;
    }

    /**
     * gets cart data from helper
     *
     * @return object
     */
    private function getCartData()
    {
        return $this->helper->getCartData();
    }
}

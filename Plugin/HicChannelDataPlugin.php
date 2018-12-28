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

namespace Magento\Braintree\Plugin;

use Magento\Braintree\Helper\HicHelper;

/**
 * Plugin for setting Braintree BN Code
 *
 * @author HiConversion <support@hiconversion.com>
 */
class HicChannelDataPlugin
{

    /**
     * @var Data
     */
    private $hicHelper;

    /**
     * @param HicHelper $hicHelper
     */
    public function __construct(
        HicHelper $hicHelper
    ) {
        $this->hicHelper = $hicHelper;
    }

    /**
     * BN code getter
     *
     * @return string
     */
    public function afterBuild($buildSubject, $result)
    {
        $newBnCode = $this->hicHelper->getBNCode();
        if (!empty($newBnCode)) {
            $result = [
                'channel' => $newBnCode
            ];
        }
        return $result;
    }
}

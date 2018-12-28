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

namespace Magento\Braintree\Helper;

use Magento\Braintree\Model\Hic\Data;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Store\Model\ScopeInterface;

/**
 * Integration data helper
 *
 * @author HiConversion <support@hiconversion.com>
 */
class HicHelper extends AbstractHelper
{
    /**
     * enabled id in configuration
     */
    const SETTINGS_ENABLED = 'hiconversion/configuration/enabled';

    /**
     * site ID in configuration
     */
    const SETTINGS_SITE_ID = 'hiconversion/configuration/site_id';

    /**
     * bn code in configuration
     */
    const SETTINGS_BN_CODE = 'hiconversion/configuration/bn_code';

    /**
     * @var Data
     */
    private $hicModel;

    /**
     * @var ProductMetadata
     */
    private $productMetadata;

    /**
     * @param Context $context
     * @param Data $hicModel
     * @param ProductMetadata $productMetadata
     */
    public function __construct(
        Context $context,
        Data $hicModel,
        ProductMetadata $productMetadata
    ) {
        $this->hicModel = $hicModel;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }
  
    /**
     * Returns Site ID from Configuration
     *
     * @return string
     */
    public function getSiteId()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_SITE_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns BN Code from Configuration
     *
     * @return string
     */
    public function getBNCode()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_BN_CODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns Url with Site ID from Configuration included
     *
     * @return string
     */
    public function getDeployUrl()
    {
        return '//h30-deploy.hiconversion.com/origin/tag/' . $this->getSiteId();
    }

    /**
     * Determines if module is enabled or not
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(self::SETTINGS_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns Magento Version
     *
     * @return string
     */
    public function getMageVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Returns Magento Edition
     *
     * @return string
     */
    public function getMageEdition()
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Returns Data that can be cached relative to a page
     * currently page and product data
     * @return object
     */
    public function getPageData()
    {
        if ($this->hicModel->isProduct()) {
            $this->hicModel->populateProductData();
        }

        $this->hicModel->populatePageData();

        return $this->hicModel;
    }
    
    /**
     * Returns Cart Data
     * @return object
     */
    public function getCartData()
    {
        $this->hicModel->populateCartData();

        return $this->hicModel->getData('cart');
    }

    /**
     * Returns user data
     * @return object
     */
    public function getUserData()
    {
        $this->hicModel->populateUserData();

        return $this->hicModel->getData('user');
    }

    /**
     * Returns order data
     * @return object
     */
    public function getOrderData()
    {
        if ($this->hicModel->isConfirmation()) {
            $this->hicModel->populateOrderData();
        }

        return $this->hicModel;
    }
}

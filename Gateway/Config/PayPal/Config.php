<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Config\PayPal;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\CcConfig;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_TITLE = 'title';
    const KEY_DISPLAY_ON_SHOPPING_CART = 'display_on_shopping_cart';
    const KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS = 'allow_shipping_address_override';
    const KEY_MERCHANT_NAME_OVERRIDE = 'merchant_name_override';
    const KEY_REQUIRE_BILLING_ADDRESS = 'require_billing_address';
    const KEY_PAYEE_EMAIL = 'payee_email';
    const KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT = 'disabled_funding_checkout';
    const KEY_PAYPAL_DISABLED_FUNDING_CART = 'disabled_funding_cart';
    const KEY_PAYPAL_DISABLED_FUNDING_PDP = 'disabled_funding_productpage';
    const BUTTON_AREA_CART = 'cart';
    const BUTTON_AREA_CHECKOUT = 'checkout';
    const BUTTON_AREA_PDP = 'productpage';
    const KEY_BUTTON_COLOR = 'color';
    const KEY_BUTTON_SHAPE = 'shape';
    const KEY_BUTTON_SIZE = 'size';
    const KEY_BUTTON_LAYOUT = 'layout';

    /**
     * @var CcConfig
     */
    private $ccConfig;

    /**
     * @var array
     */
    private $icon = [];

    /**
     * @var \Magento\Braintree\Model\Config\Source\Size
     */
    private $sizeConfigSource;

    /**
     * @var \Magento\Braintree\Model\Config\Source\Color
     */
    private $colorConfigSource;

    /**
     * @var \Magento\Braintree\Model\Config\Source\Shape
     */
    private $shapeConfigSource;

    /**
     * @var \Magento\Braintree\Model\Config\Source\Layout
     */
    private $layoutConfigSource;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CcConfig $ccConfig
     * @param null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CcConfig $ccConfig,
        \Magento\Braintree\Model\Config\Source\Size $sizeConfigSource,
        \Magento\Braintree\Model\Config\Source\Color $colorConfigSource,
        \Magento\Braintree\Model\Config\Source\Shape $shapeConfigSource,
        \Magento\Braintree\Model\Config\Source\Layout $layoutConfigSource,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->ccConfig = $ccConfig;
        $this->sizeConfigSource = $sizeConfigSource;
        $this->colorConfigSource = $colorConfigSource;
        $this->shapeConfigSource = $shapeConfigSource;
        $this->layoutConfigSource = $layoutConfigSource;
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isDisplayShoppingCart()
    {
        return (bool) $this->getValue(self::KEY_DISPLAY_ON_SHOPPING_CART);
    }

    /**
     * Is shipping address can be editable on PayPal side
     *
     * @return bool
     */
    public function isAllowToEditShippingAddress()
    {
        return (bool) $this->getValue(self::KEY_ALLOW_TO_EDIT_SHIPPING_ADDRESS);
    }

    /**
     * Get merchant name to display in PayPal popup
     *
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getValue(self::KEY_MERCHANT_NAME_OVERRIDE);
    }

    /**
     * Is billing address can be required
     *
     * @return string
     */
    public function isRequiredBillingAddress()
    {
        return $this->getValue(self::KEY_REQUIRE_BILLING_ADDRESS);
    }

    /**
     * Get title of payment
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getValue(self::KEY_TITLE);
    }

    /**
     * Get payee email
     *
     * @return string
     */
    public function getPayeeEmail()
    {
        return $this->getValue(self::KEY_PAYEE_EMAIL);
    }

    /**
     * Retrieve the button style config values
     * @param $area
     * @param $style
     * @return mixed|string
     */
    private function getButtonStyle($area, $style)
    {
        $useCustom = $this->getValue("button_customise_" . $area);
        if ($useCustom) {
            $value = $this->getValue("button_" . $style . "_" . $area);
        } else {
            $defaults = [
                'button_color_cart' => 2,
                'button_layout_cart' => 1,
                'button_size_cart' => 2,
                'button_shape_cart' => 1,
                'button_color_checkout' => 2,
                'button_layout_checkout' => 1,
                'button_size_checkout' => 2,
                'button_shape_checkout' => 1,
                'button_color_productpage' => 2,
                'button_layout_productpage' => 0,
                'button_size_productpage' => 2,
                'button_shape_productpage' => 1
            ];
            $value = $defaults["button_" . $style . "_" . $area];
        }

        return $value;
    }

    /**
     * Get button color mapped to the value expected by the PayPal API
     * @return string
     */
    public function getButtonColor($area=self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_COLOR);
        $options = $this->colorConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button shape mapped to the value expected by the PayPal API
     * @return string
     */
    public function getButtonShape($area=self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_SHAPE);
        $options = $this->shapeConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button size mapped to the value expected by the PayPal API
     * @return string
     */
    public function getButtonSize($area=self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_SIZE);
        $options = $this->sizeConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get button layout mapped to the value expected by the PayPal API
     * @return string
     */
    public function getButtonLayout($area=self::BUTTON_AREA_CART)
    {
        $value = $this->getButtonStyle($area, self::KEY_BUTTON_LAYOUT);
        $options = $this->layoutConfigSource->toRawValues();
        return $options[$value];
    }

    /**
     * Get PayPal icon
     * @return array
     */
    public function getPayPalIcon()
    {
        if (empty($this->icon)) {
            $asset = $this->ccConfig->createAsset('Magento_Braintree::images/paypal.png');
            list($width, $height) = getimagesize($asset->getSourceFile());
            $this->icon = [
                'url' => $asset->getUrl(),
                'width' => $width,
                'height' => $height
            ];
        }

        return $this->icon;
    }

    /**
     * Disabled paypal funding options - Card
     * @param $area self::KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT or KEY_PAYPAL_DISABLED_FUNDING_CART
     * @return string
     */
    public function getDisabledFundingOptionCard($area = null)
    {
        if (!$area) {
            $area = self::KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT;
        }
        return strstr($this->getValue($area), "card") ? true : false;
    }

    /**
     * Disabled paypal funding options - ELV
     * @param $area self::KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT or KEY_PAYPAL_DISABLED_FUNDING_CART
     * @return bool
     */
    public function getDisabledFundingOptionElv($area = null)
    {
        if (!$area) {
            $area = self::KEY_PAYPAL_DISABLED_FUNDING_CHECKOUT;
        }
        return strstr($this->getValue($area), "elv") ? true : false;
    }

    /**
     * PayPal btn enabled product page
     * @return bool
     */
    public function getProductPageBtnEnabled()
    {
        return $this->getValue('button_productpage_enabled');
    }
}


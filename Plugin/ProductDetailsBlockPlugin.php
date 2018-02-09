<?php

namespace Magento\Braintree\Plugin;

/**
 * Class ProductDetailsBlockPlugin
 * @package Magento\Braintree\Plugin
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class ProductDetailsBlockPlugin
{
    protected $listingBlock;

    public function __construct(
        \Magento\Braintree\Block\Credit\Calculator\Listing\Product $listingBlock
    ) {
        $this->listingBlock = $listingBlock;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param callable $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function aroundGetProductDetailsHtml(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        callable $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $result = $proceed($product);

        if ($product) {
            $this->listingBlock->setProduct($product);
            $result .= $this->listingBlock->toHtml();
        }

        return $result;
    }
}

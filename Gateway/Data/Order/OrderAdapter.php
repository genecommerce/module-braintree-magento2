<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Data\Order;

use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderAdapter implements OrderAdapterInterface
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param Order $order
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        Order $order,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->order = $order;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Returns currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->order->getBaseCurrencyCode();
    }

    /**
     * Check whether order is multi shipping
     *
     * @return bool
     */
    public function isMultishipping()
    {
        $quoteId = $this->order->getQuoteId();
        if (!$quoteId) {
            return false;
        }
        $quote = $this->quoteRepository->get($quoteId);

        return (bool)$quote->getIsMultiShipping();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->order->getIncrementId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->order->getCustomerId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|\Magento\Sales\Api\Data\OrderAddressInterface|null
     */
    public function getBillingAddress()
    {
        if ($this->order->getBillingAddress()) {
            return $this->order->getBillingAddress();
        }

        return null;
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|Order\Address|null
     */
    public function getShippingAddress()
    {
        if ($this->order->getShippingAddress()) {
            return $this->order->getShippingAddress();
        }

        return null;
    }

    /**
     * Returns order store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->order->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->order->getEntityId();
    }

    /**
     * Returns order grand total amount
     *
     * @return float|null
     */
    public function getGrandTotalAmount()
    {
        return $this->order->getBaseGrandTotal();
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getItems()
    {
        return $this->order->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp()
    {
        return $this->order->getRemoteIp();
    }

    /**
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->order->getBaseDiscountAmount();
    }

    /**
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->order->getBaseTaxAmount();
    }
}

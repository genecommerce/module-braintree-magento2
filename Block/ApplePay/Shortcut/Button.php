<?php

namespace Magento\Braintree\Block\ApplePay\Shortcut;

use Magento\Braintree\Block\ApplePay\AbstractButton;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Button
 * @package Magento\Braintree\Model\ApplePay\Shortcut
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Button extends AbstractButton implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var DefaultConfigProvider
     */
    private $defaultConfigProvider;

    /**
     * Button constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param MethodInterface $payment
     * @param \Magento\Braintree\Model\ApplePay\Auth $auth
     * @param DefaultConfigProvider $defaultConfigProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        MethodInterface $payment,
        \Magento\Braintree\Model\ApplePay\Auth $auth,
        DefaultConfigProvider $defaultConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $payment, $auth, $data);
        $this->defaultConfigProvider = $defaultConfigProvider;
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * Current Quote ID for guests
     * @return int
     */
    public function getQuoteId()
    {
        try {
            $config = $this->defaultConfigProvider->getConfig();
            if (!empty($config['quoteData']['entity_id'])) {
                return $config['quoteData']['entity_id'];
            }
        } catch (NoSuchEntityException $e) {
            if ($e->getMessage() !== 'No such entity with cartId = ') {
                throw $e;
            }
        }

        return 0;
    }
}

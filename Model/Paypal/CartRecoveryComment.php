<?php

namespace Magento\Braintree\Model\Paypal;

use Magento\Config\Model\Config\CommentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CartRecoveryComment
 *
 * @package Magento\Braintree\Model\Paypal
 * @author Paul Canning <paul.canning@gene.co.uk>
 */
class CartRecoveryComment implements CommentInterface
{
    const PAYPAL_CART_RECOVERY_URLS = [
        'sandbox' => 'https://www.sandbox.paypal.com/cartrecovery/signup?partnerUrl=',
        'production' => 'https://www.paypal.com/cartrecovery/signup?partnerUrl='
    ];
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CartRecoveryComment constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $elementValue
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCommentText($elementValue)
    {
        $cartRecoveryUrl = self::PAYPAL_CART_RECOVERY_URLS[$elementValue];
        $url = $cartRecoveryUrl . $this->storeManager->getStore()->getBaseUrl();

        return sprintf('Please click <a href="%s">here</a> to complete the on-boarding process.', $url);
    }
}

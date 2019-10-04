<?php
declare(strict_types=1);

namespace Magento\Braintree\Controller\Payment;

use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Class UpdatePaymentMethod
 */
class UpdatePaymentMethod extends Action implements HttpGetActionInterface
{
    /**
     * @var BraintreeAdapter
     */
    private $adapter;
    /**
     * @var PaymentTokenManagementInterface
     */
    private $tokenManagement;
    /**
     * @var Session
     */
    private $session;

    /**
     * UpdatePaymentMethod constructor.
     *
     * @param Context $context
     * @param BraintreeAdapter $adapter
     * @param PaymentTokenManagementInterface $tokenManagement
     * @param Session $session
     */
    public function __construct(
        Context $context,
        BraintreeAdapter $adapter,
        PaymentTokenManagementInterface $tokenManagement,
        Session $session
    ) {
        parent::__construct($context);
        $this->adapter = $adapter;
        $this->tokenManagement = $tokenManagement;
        $this->session = $session;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $publicHash = $this->getRequest()->getParam('public_hash');
        $nonce = $this->getRequest()->getParam('nonce');

        $customerId = $this->session->getCustomerId();

        $paymentToken = $this->tokenManagement->getByPublicHash($publicHash, $customerId);

        $result = $this->adapter->updatePaymentMethod(
            $paymentToken->getGatewayToken(),
            [
                'paymentMethodNonce' => $nonce,
                'options' => [
                    'verifyCard' => true
                ]
            ]
        );

        $response->setData(['success' => (bool) $result->success]);

        return $response;
    }
}

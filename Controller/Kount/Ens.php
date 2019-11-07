<?php
declare(strict_types=1);

namespace Magento\Braintree\Controller\Kount;

use Magento\Braintree\Model\Kount\EnsConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Class Index
 */
class Ens extends Action
{
    const KOUNT_MERCHANT_ID = 'payment/braintree/kount_id';
    /**
     * @var EnsConfig
     */
    private $ensConfig;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param EnsConfig $ensConfig
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        EnsConfig $ensConfig,
        RemoteAddress $remoteAddress
    ) {
        parent::__construct($context);
        $this->ensConfig = $ensConfig;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->isAllowed()) {
            $response->setHttpResponseCode(401);
        }

        $request = file_get_contents('php://input'); // @codingStandardsIgnoreLine
        $xml = simplexml_load_string($request);

        if (empty($xml['merchant'])) {
            throw new LocalizedException(__('Invalid ENS XML'));
        }

        if (!$this->ensConfig->validateMerchantId((int)$xml['merchant'])) {
            throw new LocalizedException(__('Invalid Merchant ID'));
        }

        foreach ($xml->children() as $event) {
            $this->ensConfig->processEvent($event);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        return $this->ensConfig->isSandbox() || $this->ensConfig->isAllowed($this->remoteAddress->getRemoteAddress());
    }
}

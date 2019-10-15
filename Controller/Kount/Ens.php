<?php
declare(strict_types=1);

namespace Magento\Braintree\Controller\Kount;

use Magento\Braintree\Model\Kount\EnsConfig;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Class Index
 */
class Ens extends Action
{
    /**
     * @var EnsConfig
     */
    private $ensConfig;
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param EnsConfig $ensConfig
     * @param RemoteAddress $remoteAddress
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Context $context,
        EnsConfig $ensConfig,
        RemoteAddress $remoteAddress,
        ManagerInterface $eventManager
    ) {
        parent::__construct($context);
        $this->ensConfig = $ensConfig;
        $this->remoteAddress = $remoteAddress;
        $this->eventManager = $eventManager;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->isAllowed()) {
            $response->setHttpResponseCode(401);
        }

        $request = file_get_contents('php://input'); // @codingStandardsIgnoreLine
        $xml = simplexml_load_string($request);

        foreach ($xml->children() as $event) {
            $this->eventManager->dispatch('braintree_kount_ens_event', ['event' => $event]);
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

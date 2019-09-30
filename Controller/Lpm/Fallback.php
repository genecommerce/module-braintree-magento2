<?php
declare(strict_types=1);

namespace Magento\Braintree\Controller\Lpm;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Class Fallback
 */
class Fallback extends Action
{
    public function execute()
    {
        $requestData = $this->getRequest()->getParams();

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        /** @var \Magento\Braintree\Block\Lpm\Fallback $fallBackBlock */
        $fallBackBlock = $resultPage->getLayout()->getBlock('braintree.lpm.fallback');
        $fallBackBlock->setFallbackData($requestData);

        return $resultPage;
    }
}

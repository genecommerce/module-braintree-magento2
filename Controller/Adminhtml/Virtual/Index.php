<?php

namespace Magento\Braintree\Controller\Adminhtml\Virtual;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 * @package Magento\Braintree\Controller\Adminhtml\Virtual
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Index extends Action
{
    const ADMIN_RESOURCE = 'Magento_Sales::create';

    /**
     * @var PageFactory $resultPageFactory
     */
    protected $resultPageFactory;

    /**
     * Index constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Braintree::virtual_terminal');
        $resultPage->getConfig()->getTitle()->prepend(__('Braintree Virtual Terminal'));

        return $resultPage;
    }
}

<?php

namespace Razoyo\CarProfile\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlInterface;

class Index extends Action
{
    protected PageFactory $resultPageFactory;
    protected Session $customerSession;
    protected UrlInterface $urlBuilder;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectUrl = $this->urlBuilder->getUrl('customer/account/login');
            $resultRedirect->setUrl($redirectUrl);
            return $resultRedirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Car Profile'));
        return $resultPage;

    }
}

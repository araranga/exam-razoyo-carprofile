<?php

namespace Razoyo\CarProfile\Controller\Account;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Message\ManagerInterface;

class Save implements ActionInterface
{
    private Session $customerSession;
    private CustomerRepositoryInterface $customerRepository;
    private RedirectFactory $resultRedirectFactory;
    private ManagerInterface $messageManager;
    private RequestInterface $request;
    private CustomerExtensionFactory $customerExtensionFactory;

    public function __construct(
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        RequestInterface $request,
        CustomerExtensionFactory $customerExtensionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->customerExtensionFactory = $customerExtensionFactory;
    }

    public function execute(): Redirect
    {
        $carId = $this->request->getParam('car');
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->customerSession->isLoggedIn()) {
            try {
                $customerId = $this->customerSession->getCustomer()->getId();
                $customer = $this->customerRepository->getById($customerId);

                $customer->setCustomAttribute('car_id_tosave', $carId);

                $this->customerRepository->save($customer);

                // Success message
                $this->messageManager->addSuccessMessage(__('Car attribute saved successfully.'));
            } catch (Exception $e) {
                // Error handling
                $this->messageManager->addErrorMessage(__('Error saving car attribute: ') . $e->getMessage());
            }
        } else {
            // Customer not logged in
            $this->messageManager->addErrorMessage(__('Please login.'));
        }

        // Redirect to the form page
        return $resultRedirect->setPath('*/*/index');
    }
}

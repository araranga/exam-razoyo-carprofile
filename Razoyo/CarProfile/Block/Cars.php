<?php

namespace Razoyo\CarProfile\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;

class Cars extends Template
{
    private CustomerSession $customerSession;

    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        \Razoyo\CarProfile\Service\VehicleService $carService,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->carService = $carService;
        $this->customerSession = $customerSession;
    }

    public function getCustomerId(): ?int
    {
        return $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomerId() : null;
    }

    public function getCarId(): ?string
    {
        $customer = $this->customerSession->getCustomer();
        return $customer->getCarIdTosave() == '' ? '' : $customer->getCarIdTosave();
    }

    public function getCars(): array
    {
        return $this->carService->fetchVehicleList()['cars'];
    }

    public function getCarDetails(): array
    {
        return $this->carService->fetchVehicleDetails($this->getCarId());
    }


     public function getCarDetailsDebug($id)
    {
        return $this->carService->fetchVehicleDetails($id);
    }   

}

<?php

namespace Razoyo\CarProfile\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    private CustomerSetupFactory $customerSetupFactory;
    private AttributeSetFactory $attributeSetFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $attribute_code = "car_id_tosave";
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var AttributeSet $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeSet->load($attributeSetId);

        // Add the 'car_id_tosave' attribute to the customer entity
        $customerSetup->addAttribute(Customer::ENTITY, $attribute_code, [
            'type' => 'varchar',
            'label' => 'Car ID Test',
            'input' => 'text',
            'required' => false,
            'visible' => true,
            'user_defined' => true, // Allows the attribute to be modified
            'system' => false,
            'position' => 100,
        ]);

        // Get the newly created attribute
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attribute_code);

        // Set the attribute to the default attribute set and the 'General' attribute group
        $attribute->setData('attribute_set_id', $customerSetup->getDefaultAttributeSetId(Customer::ENTITY));
        $attribute->setData('attribute_group_id', $customerSetup->getDefaultAttributeGroupId(Customer::ENTITY));

        // Add the attribute to forms
        $attribute->setData('used_in_forms', [
            'adminhtml_customer', // Admin customer edit form
            'customer_account_create', // Customer registration form
            'customer_account_edit' // Customer account edit form
        ]);

        // Save the attribute
        $attribute->save();

        $setup->endSetup();
    }
}
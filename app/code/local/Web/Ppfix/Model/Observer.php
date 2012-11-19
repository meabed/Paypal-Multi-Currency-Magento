<?php
class Web_Ppfix_Model_Observer
{
    public function setConfig(Varien_Event_Observer $observer)
    {
        $currentMerchant = Mage::getStoreConfig('paypal/general/business_account');
        $ppMerchant = Mage::helper('ppfix')->getConfig('business_account');
        if($currentMerchant != $ppMerchant){
            $config = new Mage_Core_Model_Config();
            $config->saveConfig('paypal/general/business_account', $ppMerchant , 'default', 0);
        }

    }
}
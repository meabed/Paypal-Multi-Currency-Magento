<?php

class Web_Ppfix_Model_Observer
{
    public function setConfig(Varien_Event_Observer $observer)
    {
        $currentMerchant = Mage::getStoreConfig('paypal/general/business_account');
        $ppMerchant = Mage::helper('ppfix')->getConfig('business_account');
        if ($currentMerchant != $ppMerchant) {
            $config = new Mage_Core_Model_Config();
            $config->saveConfig('paypal/general/business_account', $ppMerchant, 'default', 0);
        }

    }

    public function setPaymentInfo(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        $payment = $order->getPayment();
        $code = $payment->getMethod();
        if (in_array($code, array('paypal_standard'))) {
            $payment->setAdditionalInformation('payment_currency', Mage::helper('ppfix')->getToCurrency());
            $payment->setAdditionalInformation('due_amount', Mage::helper('ppfix')->convertAmount($order->getBaseGrandTotal()));
            $payment->setAdditionalInformation('exchange_rate', Mage::helper('ppfix')->getCurrentExchangeRate());
        }
        $payment->save();
    }

    public function getPaymentInfo(Varien_Event_Observer $observer)
    {
        $transport = $observer->getTransport();
        $payment = $observer->getPayment();
        if ($payment->getAdditionalInformation('payment_currency')) {
            $transport['Payment Currency'] = $payment->getAdditionalInformation('payment_currency');
        }
        if ($payment->getAdditionalInformation('due_amount')) {
            $transport['Amount Due'] = $payment->getAdditionalInformation('due_amount');

        }
        if ($payment->getAdditionalInformation('exchange_rate')) {
            $transport['Exchange Rate'] = $payment->getAdditionalInformation('exchange_rate');

        }
        return;
    }
}
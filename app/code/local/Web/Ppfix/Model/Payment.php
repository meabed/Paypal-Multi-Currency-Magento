<?php

class Web_Ppfix_Model_Payment extends Mage_Sales_Model_Order_Payment
{

    /**
     * Process a capture notification from a payment gateway for specified amount
     * Creates an invoice automatically if the amount covers the order base grand total completely
     * Updates transactions hierarchy, if required
     * Prevents transaction double processing
     * Updates payment totals, updates order status and adds proper comments
     *
     * TODO: eliminate logic duplication with capture()
     *
     * @param float $amount
     * @param bool  $skipFraudDetection
     * @return Mage_Sales_Model_Order_Payment
     */
    public function registerCaptureNotification($amount, $skipFraudDetection = false)
    {
        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE,
            $this->getAuthorizationTransaction()
        );

        $order = $this->getOrder();
        $amount = (float)$amount;
        //DD
        $currency = Mage::getModel('directory/currency')->load(Mage::helper('ppfix')->getToCurrency());
        Mage::log('Currency:' . $currency->getCurrencyCode());
        Mage::log($currency->getCurrencyCode());
        Mage::log('amount:' . strval($amount));
        Mage::log('grand total:' . strval((float)$order->getBaseGrandTotal()));
        if ($currency->getCurrencyCode() != Mage::app()->getStore()->getBaseCurrencyCode()) {
            $amount = (float)$order->getBaseGrandTotal();
        }

        //--DD
        $invoice = $this->_getInvoiceForTransactionId($this->getTransactionId());

        // register new capture
        if (!$invoice) {
            if ($this->_isCaptureFinal($amount)) {
                $invoice = $order->prepareInvoice()->register();
                $order->addRelatedObject($invoice);
                $this->setCreatedInvoice($invoice);
            } else {
                if (!$skipFraudDetection) {
                    $this->setIsFraudDetected(true);
                }
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
            }
        }

        $status = true;
        if ($this->getIsTransactionPending()) {
            $message = Mage::helper('sales')->__('Capturing amount of %s is pending approval on gateway.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            if ($this->getIsFraudDetected()) {
                $message = Mage::helper('sales')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
        } else {
            $message = Mage::helper('sales')->__('Registered notification about captured amount of %s.', $this->_formatPrice($amount));
            $state = Mage_Sales_Model_Order::STATE_PROCESSING;
            if ($this->getIsFraudDetected()) {
                $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
                $message = Mage::helper('sales')->__('Order is suspended as its capture amount %s is suspected to be fraudulent.', $this->_formatPrice($amount));
                $status = Mage_Sales_Model_Order::STATUS_FRAUD;
            }
            // register capture for an existing invoice
            if ($invoice && Mage_Sales_Model_Order_Invoice::STATE_OPEN == $invoice->getState()) {
                $invoice->pay();
                $this->_updateTotals(array('base_amount_paid_online' => $amount));
                $order->addRelatedObject($invoice);
            }
        }

        $transaction = $this->_addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, $invoice, true);
        $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState($state, $status, $message);
        return $this;
    }
}

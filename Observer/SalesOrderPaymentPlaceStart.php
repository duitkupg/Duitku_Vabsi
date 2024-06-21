<?php
/**
 * Copyright (c) 2017. All rights reserved Duitku Vabsi Host to Host.
 *
 * This program is free software. You are allowed to use the software but NOT allowed to modify the software.
 * It is also not legal to do any changes to the software and distribute it in your own name / brand.
 *
 * All use of the payment modules happens at your own risk. We offer a free test account that you can use to test the module.
 *
 * @author    Duitku Vabsi
 * @copyright Duitku Vabsi (http://duitku.com)
 * @license   Duitku Vabsi
 *
 */
namespace Duitku\Vabsi\Observer;
use \Duitku\Vabsi\Model\Method\Epay\Payment as EpayPayment;

class SalesOrderPaymentPlaceStart implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Sales Order Payment Place Start Observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $payment = $observer['payment'];
        if ($payment->getMethod() == EpayPayment::METHOD_CODE) {
            $order = $payment->getOrder();
            $order->setCanSendNewEmailFlag(false);
            $order->setIsCustomerNotified(false);
            $order->save();
        }
    }
}

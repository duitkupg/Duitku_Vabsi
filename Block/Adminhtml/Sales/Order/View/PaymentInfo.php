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
namespace Duitku\Vabsi\Block\Adminhtml\Sales\Order\View;

use Duitku\Vabsi\Model\Method\Epay\Payment as EpayPayment;
use Duitku\Vabsi\Helper\DuitkuConstants;

class PaymentInfo extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_priceHelper;

    /**
     * @var \Duitku\Vabsi\Helper\Data
     */
    protected $_duitkuHelper;

    /**
     * PaymentInfo constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Duitku\Vabsi\Helper\Data $duitkuHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Duitku\Vabsi\Helper\Data $duitkuHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
        $this->_priceHelper = $priceHelper;
        $this->_duitkuHelper = $duitkuHelper;
    }
    /**
     * @return string
     */
    protected function _toHtml()
    {
        return ($this->getOrder()->getPayment()->getMethod() === EpayPayment::METHOD_CODE) ? parent::_toHtml() : '';
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_registry->registry('current_order');
    }

    /**
     * Display transaction data
     *
     * @return string
     */
    public function getTransactionData()
    {
        $result ='';
        $order = $this->getOrder();
        $storeId = $order->getStoreId();
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();

       if ($paymentMethod === EpayPayment::METHOD_CODE) {
            /** @var \Duitku\Vabsi\Model\Method\Epay\Payment */
            $ePayMethod = $payment->getMethod();

            if (isset($ePayMethod)) {
                $transactionId = $payment->getAdditionalInformation($ePayMethod::METHOD_REFERENCE);
                if (!empty($transactionId)) {
                    $message = "";
                    $transaction = $ePayMethod->getTransaction($transactionId, $message);

                    if (isset($transaction)) {
                        $result = $this->createEpayTransactionHtml($transaction, $order);
                    // } elseif ($ePayMethod->getConfigData(DuitkuConstants::REMOTE_INTERFACE, $storeId) == 0) {
                    //     $result = '';
                    } else {
                        $result = '';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Create Checkout Transaction HTML
     *
     * @param \Duitku\Vabsi\Model\Api\Checkout\Response\Models\Transaction $transaction
     * @return string
     */
   
    /**
     * Set the first letter to uppercase
     *
     * @param string $status
     * @return string
     */
    public function checkoutStatus($status)
    {
        if (!isset($status)) {
            return "";
        }
        $firstLetter = substr($status, 0, 1);
        $firstLetterToUpper = strtoupper($firstLetter);
        $result = str_replace($firstLetter, $firstLetterToUpper, $status);

        return $result;
    }

    /**
     * Create html for paymentLogoUrl
     *
     * @param mixed $paymentId
     * @return string
     */
    public function getPaymentLogoUrl($paymentId)
    {
        return '<img class="duitku_paymentcard" style="width:100px;" src="https://www.duitku.com/wp-content/uploads/2017/03/Duitku-Logo-small-300x149.jpg"';
    }

    /**
     * Create ePay Transaction HTML
     *
     * @param \Duitku\Vabsi\Model\Api\Epay\Response\Models\TransactionInformationType $transactionInformation
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function createEpayTransactionHtml($transactionInformation, $order)
    {
            
        if (isset($transactionInformation->history) && isset($transactionInformation->history->TransactionHistoryInfo) && count($transactionInformation->history->TransactionHistoryInfo) > 0) {
            // Important to convert this item to array. If only one item is to be found in the array of history items
            // the object will be handled as non-array but object only.
            $historyArray = $transactionInformation->history->TransactionHistoryInfo;
            if (count($transactionInformation->history->TransactionHistoryInfo) == 1) {
                // convert to array
                $historyArray = array($transactionInformation->history->TransactionHistoryInfo);
            }
            $res = '<br /><br />';
            $res .= '<tr><td colspan="2" class="duitku_table_title duitku_table_title_padding">' . __("History") . '</td></tr>';
            foreach ($historyArray as $history) {
                $res .= '<tr class="duitku_table_history_tr"><td class="duitku_table_history_td">' . str_replace('T', ' ', $history->created) . '</td>';
                $res .= '<td>';
                if (strlen($history->username) > 0) {
                    $res .= ($history->username . ': ');
                }
                $res .= $history->eventMsg . '</td></tr>';
            }
        }

        return $res;
    }
}

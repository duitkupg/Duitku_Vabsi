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
namespace Duitku\Vabsi\Controller;

use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Payment\Transaction;
use \Duitku\Vabsi\Helper\DuitkuConstants;
use \Duitku\Vabsi\Model\Method\Epay\Payment as EpayPayment;
use \Magento\Framework\Controller\ResultFactory;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\Request\InvalidRequestException;

abstract class AbstractActionController extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
	/**
	* @var \Magento\Sales\Model\OrderFactory
	*/
	protected $_orderFactory;

	/**
	* @var \Magento\Checkout\Model\Session
	*/
	protected $_checkoutSession;

	/**
	* @var \Duitku\Vabsi\Helper\Data
	*/
	protected $_duitkuHelper;

	/**
	* @var \Magento\Framework\Controller\Result\JsonFactory
	*/
	protected $_resultJsonFactory;

	/**
	* @var \Duitku\Vabsi\Logger\DuitkuLogger
	*/
	protected $_duitkuLogger;

	/**
	* @var \Magento\Payment\Helper\Data
	*/
	protected $_paymentHelper;

	/**
	* @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
	*/
	protected $_orderSender;

	/**
	* @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
	*/
	protected $_invoiceSender;

	/**
	* AbstractActionController constructor.
	*
	* @param \Magento\Framework\App\Action\Context $context
	* @param \Magento\Sales\Model\OrderFactory $orderFactory
	* @param \Magento\Checkout\Model\Session $checkoutSession
	* @param \Duitku\Vabsi\Helper\Data $duitkuHelper
	* @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	* @param \Duitku\Vabsi\Logger\DuitkuLogger $duitkuLogger
	* @param \Magento\Payment\Helper\Data $paymentHelper
	* @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
	* @param \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
	*/
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Checkout\Model\Session $checkoutSession,
		\Duitku\Vabsi\Helper\Data $duitkuHelper,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Duitku\Vabsi\Logger\DuitkuLogger $duitkuLogger,
		\Magento\Payment\Helper\Data $paymentHelper,
		\Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
		\Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
	){
		parent::__construct($context);
		$this->_orderFactory = $orderFactory;
		$this->_checkoutSession = $checkoutSession;
		$this->_duitkuHelper = $duitkuHelper;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_scopeConfig = $scopeConfig;
		$this->_duitkuLogger = $duitkuLogger;
		$this->_paymentHelper = $paymentHelper;
		$this->_orderSender = $orderSender;
		$this->_invoiceSender = $invoiceSender;
	}

	/** * @inheritDoc */ 
	public function createCsrfValidationException( RequestInterface $request ): ?       InvalidRequestException { 
         return null; 
	} 
	/** * @inheritDoc */ 
	public function validateForCsrf(RequestInterface $request): ?bool {     
		return true; 
	}
	/**
	* Get order object
	*
	* @return \Magento\Sales\Model\Order
	*/
	protected function _getOrder(){
		$incrementId = $this->_checkoutSession->getLastRealOrderId();
		return $this->getOrder($incrementId);
	}

	/**
	* Get order by IncrementId
	*
	* @param $incrementId
	* @return \Magento\Sales\Model\Order
	*/
	protected function _getOrderByIncrementId($incrementId){
		return $this->getOrder($incrementId);
	}

	/**
	* Get order object
	* @param mixed $incrementId
	* @return \Magento\Sales\Model\Order
	*/
	public function getOrder($incrementId){
		return $this->_orderFactory->create()->loadByIncrementId($incrementId);
	}

	/**
	* Set the order details
	*
	* @param \Magento\Sales\Model\Order $order
	*/
	protected function setOrderDetails($order){
		$message = __("Order placed and is now awaiting payment authorization");
		$order->addStatusToHistory($message);
		$order->setIsNotified(false);
		// $order->save();
		$this->saveOrder($order);
	}

	protected function acceptOrder(){
		$posted = $this->getRequest()->getParams();
		$resultCode = $posted['resultCode'];
		if(isset($posted['resultCode']) && isset($posted['merchantOrderId']) && isset($posted['reference']) && ($resultCode == '00' || $resultCode == '01')){
				$order = $this->_getOrderByIncrementId($posted['merchantOrderId']);
				$this->_checkoutSession->setLastOrderId($order->getId());
				$this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
				$this->_checkoutSession->setLastQuoteId($order->getQuoteId());
				$this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
				$this->_redirect('checkout/onepage/success');
		}
		else{
			$this->cancelOrder();
		}
       
		//$this->_redirect('checkout/onepage/success');
	}

	/**
	* Cancel the order
	*/
	protected function cancelOrder(){
		
		$this->cancelCurrentOrder();
		$this->restoreQuote();
		$this->_redirect('checkout/cart');
	}

	/**
	* Cancel last placed order with specified comment message
	*
	* @return bool
	*/
	protected function cancelCurrentOrder(){
		$order = $this->_getOrder();
		if($order->getId() && $order->getState() != Order::STATE_CANCELED){
			$comment =  __("The order was canceled");
			$order->registerCancellation($comment)->save();
			return true;
		}

		return false;
	}

	/**
	* Restores quote
	*
	* @return bool
	*/
	protected function restoreQuote(){
		return $this->_checkoutSession->restoreQuote();
	}

	/**
	* Get Payment method instance object
	*
	* @param string $method
	* @return {MethodInstance}
	*/
	protected function _getPaymentMethodInstance($method){
		return $this->_paymentHelper->getMethodInstance($method);
	}

	/**
	* Process the callback data
	*
	* @param \Magento\Sales\Model\Order $order $order
	* @param \Duitku\Vabsi\Model\Method\AbstractPayment $paymentMethodInstance
	* @param string $txnId
	* @param string $methodReference
	* @param string $ccType
	* @param string $ccNumber
	* @param mixed $feeAmountInMinorUnits
	* @param mixed $minorUnits
	* @param mixed $status
	* @param \Magento\Sales\Model\Order\Payment $payment
	* @return void
	*/
	protected function _processCallbackData($order, $paymentMethodInstance, $txnId, $methodReference,$status, $payment = null){
		try{
			if(!isset($payment)){
				$payment = $order->getPayment();
			}
			$storeId = $order->getStoreId();
			$this->updatePaymentData($order, $txnId, $methodReference, $paymentMethodInstance, $status);

           
			if(!$order->getEmailSent() && $paymentMethodInstance->getConfigData(DuitkuConstants::SEND_MAIL_ORDER_CONFIRMATION, $storeId) == 1){
				$this->sendOrderEmail($order);
			}

			if($paymentMethodInstance->getConfigData(DuitkuConstants::INSTANT_INVOICE, $storeId) == 1){
				$this->createInvoice($order, $paymentMethodInstance);
			}
		} catch(\Exception $ex){
			throw $ex;
		}
	}

	/**
	* Update the order and payment informations
	*
	* @param \Magento\Sales\Model\Order $order
	* @param string $txnId
	* @param string $methodReference
	* @param string $ccType
	* @param string $ccNumber
	* @param \Duitku\Vabsi\Model\Method\AbstractPayment $paymentMethodInstance
	* @param mixed $status
	* @param mixed $fraudStatus
	* @return void
	*/
	public function updatePaymentData($order, $txnId, $methodReference, $paymentMethodInstance, $status){
		try{
			/** @var \Magento\Sales\Model\Order\Payment */
			$payment = $order->getPayment();
			$payment->setTransactionId($txnId);
			$payment->setIsTransactionClosed(false);
			$payment->setAdditionalInformation(array($methodReference => $txnId));
			$transactionComment = __("Payment authorization was a success.");
			$order->setStatus($status);
			$order->setState(Order::STATE_PROCESSING);
			$transaction = $payment->addTransaction(Transaction::TYPE_AUTH);
			$payment->addTransactionCommentsToOrder($transaction, $transactionComment);
			$order->save();
		} catch(\Exception $ex){
			throw $ex;
		}
	}

	/**
	* Add Surcharge to the order
	*
	* @param \Magento\Sales\Model\Order $order
	* @param mixed $feeAmountInMinorunits
	* @param mixed $minorunits
	* @param mixed $ccType
	* @param \Duitku\Vabsi\Model\Method\AbstractPayment $paymentMethodInstance
	* @return void
	*/
   
	/**
	* Send the orderconfirmation mail to the customer
	*
	* @param \Magento\Sales\Model\Order $order
	* @return void
	*/
	public function sendOrderEmail($order){
		try{
			$this->_orderSender->send($order);
			$order->addStatusToHistory(__("Notified customer about order #%1", $order->getId()))
			->setIsCustomerNotified(1)
			->save();
		} catch(\Exception $ex){
			$order->addStatusToHistory(__("Could not send order confirmation for order #%1", $order->getId()))
			->setIsCustomerNotified(0)
			->save();
		}
	}

	/**
	* Create an invoice
	*
	* @param \Magento\Sales\Model\Order $order
	* @param \Duitku\Vabsi\Model\Method\AbstractPayment $paymentMethodInstance
	*/
	public function createInvoice($order, $paymentMethodInstance){
		try{
			if($order->canInvoice()){
				/** @var \Magento\Sales\Model\Order\Invoice */
				$invoice = $order->prepareInvoice();
				$storeId = $order->getStoreId();
				$invoice->register();
				$invoice->save();
				$transactionSave = $this->_objectManager->create('Magento\Framework\DB\Transaction')
				->addObject($invoice)
				->addObject($invoice->getOrder());
				$transactionSave->save();

				if($paymentMethodInstance->getConfigData(DuitkuConstants::INSTANT_INVOICE_MAIL, $order->getStoreId()) == 1){
					$invoice->setEmailSent(1);
					$this->_invoiceSender->send($invoice);
					$order->addStatusToHistory(__("Notified customer about invoice #%1", $invoice->getId()))
					->setIsCustomerNotified(1)
					->save();
				}
			}
		} catch(\Exception $ex){
			throw $ex;
		}
	}

	/**
     * Save order with order repository
     *
     * @param OrderInterface $order
     * @throws \Exception
     */
    public function saveOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        try {
            $this->_orderRepository->save($order);
        } catch (\Exception $e) {
            $error_exception = "AbstractAction.class SaveOrder : " . $e;
            $this->_duitkuLogger->addRecord(500, $error_exception);
        }
    }

	/**
	* Log Error
	*
	* @param string $paymentMethod
	* @param mixed $id
	* @param mixed $errorMessage
	*/
	protected function _logError($paymentMethod, $id, $errorMessage){
		if($paymentMethod === EpayPayment::METHOD_CODE){
			$this->_duitkuLogger->addEpayError($id, $errorMessage);
		} else{
			$this->_duitkuLogger->addRecord(500, $errorMessage);
		}
	}

	/**
	* Get Callback Response
	*
	* @param mixed $statusCode
	* @param mixed $message
	* @param mixed $id
	* @return mixed
	*/
	protected function _createCallbackResult($statusCode, $message, $id){
		$result = $this->_resultJsonFactory->create();
		$result->setHttpResponseCode($statusCode);

		$result->setData(
			['id'=>$id,
				'statusCode'=>$statusCode,
				'message'=>$message]);
		
		return $result;
	}
}

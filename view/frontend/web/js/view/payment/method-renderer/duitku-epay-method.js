/*browser:true*/
/*global define*/
define(
	[
		'ko',
		'jquery',
		'Magento_Checkout/js/view/payment/default',
		'Magento_Ui/js/model/messageList',
		'mage/translate',
		'Magento_Checkout/js/model/full-screen-loader'
	],
	function(ko, $, Component, globalMessageList, $t, fullScreenLoader) {
		'use strict';

		return Component.extend({
				initialize: function() {
					this._super().initChildren();
					//this.loadEPayPaymentWindowJs();
				},
				defaults: {
					template: 'Duitku_Vabsi/payment/epay-form'
				},
				redirectAfterPlaceOrder: false,
				getDuitkuEpayTitle: function () {
					return window.checkoutConfig.payment.duitku_vabsiepay.paymentTitle;
				},
				getDuitkuEpayLogo: function () {
					return window.checkoutConfig.payment.duitku_vabsiepay.paymentLogoSrc;
				},
				getDuitkuEpayPaymentLogoSrc: function () {
					return window.checkoutConfig.payment.duitku_vabsiepay.paymentTypeLogoSrc;
				},
				afterPlaceOrder: function () {
					fullScreenLoader.startLoader();
					this.getPaymentWindow();
				},
				getPaymentWindow: function () {
					var self = this;
					var url = window.checkoutConfig.payment.duitku_vabsiepay.checkoutUrl;
				           
					$.get(url).done(function (response) {
						
							response = JSON.parse(response);
							var Url = response.url;
							$.mage.redirect(Url);
							if(!response) {
								self.showError($t("Error opening payment window"));
								$.mage.redirect(window.checkoutConfig.payment.duitku_vabsiepay.cancelUrl);
							}
							self.openPaymentWindow(response);                             
						}).fail(function(error) {
							self.showError($t("Error opening payment window") + ': ' + error.statusText);
							$.mage.redirect(window.checkoutConfig.payment.duitku_vabsiepay.cancelUrl);
						});
				},
				openPaymentWindow: function(requestString) {
					var onclose = function() {
						var cancelUrl = window.checkoutConfig.payment.duitku_vabsiepay.cancelUrl;
						$.mage.redirect(cancelUrl);
					}
					var paymentwindow = new PaymentWindow(requestString);
					if(window.checkoutConfig.payment.duitku_vabsiepay.windowState === "1") {
						paymentwindow.on("close", onclose);
					}
					paymentwindow.open();
				},
				loadEPayPaymentWindowJs: function() {
					$.getScript(window.checkoutConfig.payment.duitku_vabsiepay.paymentWindowJsUrl);
				},
				
				showError: function (errorMessage) {
					globalMessageList.addErrorMessage({
							message: errorMessage
						});
				}
			});
	}
);
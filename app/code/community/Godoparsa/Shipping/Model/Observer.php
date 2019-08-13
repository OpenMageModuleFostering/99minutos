<?php
class Godoparsa_Shipping_Model_Observer extends Mage_Core_Helper_Abstract
{
    public function sendNotificationEmailToAdmin($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $storeId = $order->getStoreId();
        $helper = Mage::helper('godoparsa_shipping');

        if (!$helper->isModuleEnabled($storeId)) {
            return;
        }

        try {
            $templateId = $helper->getEmailTemplate($storeId);
            $programado = '99minutos.com - Programado mismo día';
            $express = '99minutos.com - En menos de 99 minutos';
            $envio = $order->getShippingDescription(); 

            $mailer = Mage::getModel('core/email_template_mailer');

            if ($helper->getNotifyGeneralEmail()) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($helper->getStoreEmailAddressSenderOption('general', 'email'), $helper->getStoreEmailAddressSenderOption('general', 'name'));
                $mailer->addEmailInfo($emailInfo);
            }

            if ($helper->getNotifySalesEmail()) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($helper->getStoreEmailAddressSenderOption('sales', 'email'), $helper->getStoreEmailAddressSenderOption('sales', 'name'));
                $mailer->addEmailInfo($emailInfo);
            }

            if ($helper->getNotifySupportEmail()) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($helper->getStoreEmailAddressSenderOption('support', 'email'), $helper->getStoreEmailAddressSenderOption('support', 'name'));
                $mailer->addEmailInfo($emailInfo);
            }

            if ($helper->getNotifyCustom1Email()) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($helper->getStoreEmailAddressSenderOption('custom1', 'email'), $helper->getStoreEmailAddressSenderOption('custom1', 'name'));
                $mailer->addEmailInfo($emailInfo);
            }

            if ($helper->getNotifyCustom2Email()) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($helper->getStoreEmailAddressSenderOption('custom2', 'email'), $helper->getStoreEmailAddressSenderOption('custom2', 'name'));
                $mailer->addEmailInfo($emailInfo);
            }

            foreach ($helper->getNotifyEmails() as $entry) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($entry['email'], $entry['name']);
                $mailer->addEmailInfo($emailInfo);
            }

            $mailer->setSender(array(
                'name' => $helper->getStoreEmailAddressSenderOption('general', 'name'),
                'email' => $helper->getStoreEmailAddressSenderOption('general', 'email'),
            ));

            $mailer->setStoreId($storeId);
            $mailer->setTemplateId($templateId);
            $mailer->setTemplateParams(array(
                'order' => $order,
            ));
            if ($envio == $programado)
            {
                $mailer->send();
            }
            if ($envio == $express)
            {
                $mailer->send();
            }

        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
    public function shippedrequest($observer)
    {
    	$schedule=				'godoparsa_shipping_large';
    	$express=				'godoparsa_shipping_express';
        $shipment=	 			$observer->getEvent()->getShipment();
       	$order= 				$shipment->getOrder();
  		$shipping_method=		$order->getShippingMethod();
        if(($shipping_method == $express) or ($shipping_method == $schedule))
        {
            if ($shipping_method == 'godoparsa_shipping_large')
			{
	    		$DeliveryType = 'Programado';
			}
			else if ($shipping_method == 'godoparsa_shipping_express')
			{
	    		$DeliveryType = '99minutos';
			}	
		    $shop=				Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		    $shippingAddress=	$order->getShippingAddress();
            $address=			$shippingAddress->getstreet();
            function reduce($v1,$v2)
            {
            	return $v1.' '.$v2;
            }
            $address_finale=	array_reduce($address,'reduce');
            $city=				$shippingAddress->getcity();
            $province=			$shippingAddress->getregion();
            $zip=				$shippingAddress->getpostcode();
            $name_order=		'Orden: '.$order->getIncrementId().'';
            $email=				$order->getCustomerEmail();
            $phone=				$shippingAddress->getTelephone();
            $first_name=		$shippingAddress->getFirstname();
            $last_name=			$shippingAddress->getLastname();
		    $payment=			$order->getPayment();
		    $method=			$payment->getMethod();
		    $amount=			round($payment->getAmountOrdered(), 2);

			$content = array(
                 'Shop'				=>	$shop,
                 'DeliveryType' 	=>	$DeliveryType,
                 'Address'			=>	$address_finale,
                 'Province'			=>	$province,
                 'City'				=>	$city,
                 'PostalCode'		=>  $zip,
                 'Email'			=> 	$email,
                 'Phone'			=>	$phone,
                 'FirstName'		=>	$first_name,
                 'LastName'			=>	$last_name,
                 'PaymentMethod'	=>	$method,
                 'Amount'			=>	$amount,
                 'Order'			=>	$name_order,
             );
            $ch = curl_init('http://api.99minutos.com/magento/shipping.php');
          	curl_setopt_array($ch, array(
              	CURLOPT_POST => TRUE,
  	        	CURLOPT_RETURNTRANSFER => TRUE,
      	    	CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
      			CURLOPT_POSTFIELDS => json_encode($content)));
          	// Send the request
          	$response = curl_exec($ch);
          	curl_close($response);
        	}
    }

}
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
    public function shippedrequest( $observer)
    {
    	$programado =			'99minutos.com - Programado mismo día';
    	$express=				'99minutos.com - En menos de 99 minutos';
        $shipment=	 			$observer->getEvent()->getShipment();
       	$order= 				$shipment->getOrder();
       	$shippingAddress=		$order->getShippingAddress();
   		$shipping_method=		$order->getShippingDescription();
        if(($shipping_method == $express) or ($shipping_method == $programado))
        {
			$shop=				Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			$db =  				new mysqli("store.99minutos.com", "minutos_magento", "g9e1BqgAQtni", "minutos_api");
    		$consult_set= 		$db->query("SELECT * FROM tbl_usersmagento WHERE store_name = '$shop'");
			$resutl_set=		mysqli_fetch_array($consult_set);
            $address=			$shippingAddress->getstreet();
            function reduce($v1,$v2)
            {
            	return $v1.' '.$v2;
            }
            $address1=			array_reduce($address,"reduce");
            $city=				$shippingAddress->getcity();
            $province=			$shippingAddress->getregion();
            $zip=				$shippingAddress->getpostcode();
            $scearch=			urlencode(implode(', ', array($address1,$city,$province,$zip)));
            $request=		 	"http://maps.googleapis.com/maps/api/geocode/json?address=".$scearch."&sensor=false";
            $name=				'Orden: '.$order->getIncrementId().'';
            $store=				'Tienda:'.$resutl_set['cliente'].'';//$order->getStoreName();
            $email=				$order->getCustomerEmail();
            $phone=				$shippingAddress->getTelephone();
            $first_name=		$shippingAddress->getFirstname();
            $last_name=			$shippingAddress->getLastname();            
       		
       		$ch=curl_init();
			curl_setopt($ch, CURLOPT_URL, $request);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		    curl_setopt($ch, CURLOPT_HTTPGET, true);
			$url = curl_exec($ch);
			curl_close($ch);
			
			$decoded_data=	json_decode($url,true);
			$latitude = $decoded_data['results']['0']['geometry']['location']['lat'];
			$longitude = $decoded_data['results']['0']['geometry']['location']['lng'];
			
        	// variables
        	$db =  new mysqli("store.99minutos.com", "minutos_magento", "g9e1BqgAQtni", "minutos_api");
        	$api_key=									'23894thfpoiq10fapo93fmapo';
        	$route=										urlencode($resutl_set['route']);
        	$street_number=								urlencode($resutl_set['street_number']);
        	$neighborhood=								urlencode($resutl_set['neighborhood']);
        	$locality=									urlencode($resutl_set['locality']);
        	$administrative_area_level_1=				urlencode($resutl_set['administrative_area']);
        	$postal_code=								urlencode($resutl_set['postal_code']);
        	$country=									$locality;
        	$latlng=									urlencode($resutl_set['latlng']);
			$destination_route=							urlencode($address1);
			$destination_locality=						urlencode($city);
			$destination_administrative_area_level=		urlencode($province);
			$destination_postal_code=					urlencode($zip);
			$d_latlng=									urlencode(implode(',', array($latitude,$longitude)));
			$customer_phone=							urlencode($phone);
			$destination_country=						$country;
			$nombre=									'Cliente: '.implode(' ',array($first_name,$last_name)).'';
		
		    //Variable que pasa al sistema de 99minutos los datos en la seccion de notas
			$notes=urlencode(implode(', ', array($store,$name,$nombre)));
		
			//url que sirve para hacer la peticion de envion al sistema de 99minutos
			$request_ship=		"https://99minutos-dot-yaxiapi.appspot.com/2/delivery/request?";
			$request_ship.=		"api_key=".$api_key."&";
			$request_ship.=		"route=".$route."&";
			$request_ship.=		"street_number=".$street_number."&";
			$request_ship.=		"neighborhood=".$neighborhood."&";
			$request_ship.=		"locality=".$locality."&";
			$request_ship.=		"administrative_area_level_1=".$administrative_area_level_1."&";
			$request_ship.=		"postal_code=".$postal_code."&";
			$request_ship.=		"country=".$country."&";
			$request_ship.=		"latlng=".$d_latlng."&";
			$request_ship.=		"destination-route=".$destination_route."&";
			$request_ship.=		"destination-street_number=&";
			$request_ship.=		"destination-neighborhood=".$destination_locality."&";
			$request_ship.=		"destination-locality=".$destination_locality."&";
			$request_ship.=		"destination-administrative_area_level=".$destination_administrative_area_level."&";
			$request_ship.=		"destination-postal_code=".$destination_postal_code."&";
			$request_ship.=		"destination-country=".$destination_country."&";
			$request_ship.=		"destination-latlng=".$d_latlng."&";
			$request_ship.=		"customer_email=".$email."&";
			$request_ship.=		"customer_phone=".$customer_phone."&";
			$request_ship.=		"notification_email=&notes=".$notes."&dispatch=True";
        	//funcion curl para enviar la peticion de envio al sistema de 99minutos		
			$chr=curl_init();
			curl_setopt($chr, CURLOPT_URL, $request_ship);
			curl_setopt($chr, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($chr, CURLOPT_HEADER, false);
			curl_setopt($chr, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($chr, CURLOPT_TIMEOUT, 1);
			$url_request = curl_exec($chr);
			curl_close($chr);

    }
    }

}
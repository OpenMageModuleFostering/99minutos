<?php

class Godoparsa_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'godoparsa_shipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = Mage::getModel('shipping/rate_result');
       	$cp_pedido = $request->getDestPostcode();
	    $total_weight=  $request->getPackageWeight(); 
		$shop=				Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
      	$freeship		=	$request->getFreeShipping();
      	$country        =   $request->getDestCountryId();
      	if ($freeship == True) {
      	    $ship = 'True';
      	}
      	else {
      	    $ship = 'False';
      	}
        //Mage::log(var_export($request->debug(), TRUE));
        $content = array(
            'Shop'			=>	$shop,
            'Freeship'      => 	$ship ,
            'PostalCode'    => 	$cp_pedido,
            'TotalWeight'   => 	$total_weight,
            'Country'       =>  $country  
        );

		$ch = curl_init('http://api.99minutos.com/magento/shippingrate.php');
        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
        	CURLOPT_RETURNTRANSFER => TRUE,
        	CURLOPT_HTTPHEADER => array(
        	    'Content-Type: application/json'
    		),
    		CURLOPT_POSTFIELDS => json_encode($content)
	    ));

        // Send the request
        $response = curl_exec($ch);
        $responseData = json_decode($response, TRUE);
        curl_close($response);
        if ($responseData['Status'] == 'OK')
        {
            //Mage::log(var_export($responseData, TRUE));
        
            $schedule_data = $responseData['Rates']['Schedule'];
			$schedule = Mage::getModel('shipping/rate_result_method');
        	$schedule->setCarrier($this->_code);
        	$schedule->setCarrierTitle($schedule_data['CarrierTitle']);
        	$schedule->setMethod($schedule_data['Method']);
        	$schedule->setMethodTitle($schedule_data['Title']);
        	$schedule->setPrice($schedule_data['Price']);
        	$schedule->setCost($schedule_data['Cost']);

            $express_data = $responseData['Rates']['Express'];
        	$express = Mage::getModel('shipping/rate_result_method');
        	$express->setCarrier($this->_code);
        	$express->setCarrierTitle($express_data['CarrierTitle']);
        	$express->setMethod($express_data['Method']);
        	$express->setMethodTitle($express_data['Title']);
        	$express->setPrice($express_data['Price']);
        	$express->setCost($express_data['Cost']);
        
        
        $result->append($schedule);
        $result->append($express);
		}
		return $result;

    }

    public function getAllowedMethods()
    {
        return array(
			'standard'    =>  'Standard delivery',
			'express'     =>  'Express delivery',
        );
    }

}
?>
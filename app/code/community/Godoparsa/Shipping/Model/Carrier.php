<?php

class Godoparsa_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'godoparsa_shipping';

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
		$shop=				Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$db =  				new mysqli("54.172.87.163", "minutos", "00Minutos+01", "minutos");
    	$consult_set= 		$db->query("SELECT settings FROM tbl_usersmagento WHERE store_name = '$shop'"); 
		$resutl_set=		mysqli_fetch_array($consult_set);
	    $setting=			$resutl_set['settings'];
	    $set_on=			'on';
		if ($setting == $set_on)
		{
        	$result = Mage::getModel('shipping/rate_result');
        	$cp_pedido = $request->getDestPostcode();
	    	$total_weight=  $request->getPackageWeight(); 
        	$max_weight=    3000000000000;

	    	$consulta_cp = $db->query("SELECT codigopostal FROM tbl_postalcode WHERE codigopostal = '$cp_pedido'"); 
		    $resultado_cp = mysqli_fetch_array($consulta_cp);
	    	$cp_entrega = $resultado_cp['codigopostal'];
	    
	    
        if (($cp_pedido = $cp_entrega) and ($total_weight <= $max_weight))
        {
        $result->append($this->_getExpressRate());
        $result->append($this->_getStandardRate());   
        }

        return $result;
        }
    }

    public function getAllowedMethods()
    {
        return array(
            'standard'    =>  'Standard delivery',
            'express'     =>  'Express delivery',
        );
    }

    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('large');
        $rate->setMethodTitle('Programado mismo día');
        $rate->setPrice(85);
        $rate->setCost(0);

        return $rate;
    }

    protected function _getExpressRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('express');
        $rate->setMethodTitle('En menos de 99 minutos');
        $rate->setPrice(115);
        $rate->setCost(0);

        return $rate;
    }
}
?>
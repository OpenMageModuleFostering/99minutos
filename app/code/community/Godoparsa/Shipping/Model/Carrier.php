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
        $max_weight=    3000000000000;
    	$db =  new mysqli("store.99minutos.com", "minutos_magento", "nz%^F&{OHP2w", "minutos_api");
    	$consulta_cp = $db->query("SELECT codigopostal FROM tbl_postalcode WHERE codigopostal = '$cp_pedido'"); 
	    $resultado_cp = mysqli_fetch_array($consulta_cp);
	    $cp_entrega = $resultado_cp['codigopostal'];
	    
        if (($cp_pedido = $cp_entrega) and ($total_weight <= $max_weight))
        {
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
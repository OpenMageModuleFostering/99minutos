<?php
$installer = $this;
$installer->startSetup();

$shop=	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

$url = "http://api.99minutos.com/magento/install.php";    
$content = json_encode(array('Shop' => $shop));

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);

$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

if ( $status != 201 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}

curl_close($curl);

$inbox = Mage::getModel('adminnotification/inbox');
$severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
$title= 	"Gracias por elegir 99minutos.com. Por favor completa el proceso de alta para usar el servicio de logística web más rápido!";
$description="Para completar el proceso por favor comunicate a 99minutos.com al tel (55) 6363 1559 o envia un correo a hola@99minutos. Gracias!";
$url= null;
$inbox->add($severity, $title, $description, $url);

$installer->endSetup();
?>
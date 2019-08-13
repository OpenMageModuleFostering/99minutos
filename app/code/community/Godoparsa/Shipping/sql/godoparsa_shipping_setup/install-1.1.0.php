<?php
$installer = $this;
$installer->startSetup();

$shop=	Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$db =  new mysqli("store.99minutos.com", "minutos_magento", "g9e1BqgAQtni", "minutos_api");
$db->query(" INSERT INTO tbl_usersmagento SET store_name = '$shop'");
$inbox = Mage::getModel('adminnotification/inbox');
$severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
$title= 	"Gracias por elegir 99minutos.com. Por favor completa el proceso de alta para usar el servicio de logística web más rápido!";
$description="Para completar el proceso por favor comunicate a 99minutos.com al tel (55) 6363 1559 o envia un correo a hola@99minutos. Gracias!";
$url= null;
$inbox->add($severity, $title, $description, $url);

$installer->endSetup();
?>
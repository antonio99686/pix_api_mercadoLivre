<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');


// Dados do webhook
$webhook_data = array(
    "url" => "http://www.seusite.com/webhook",
    "event_types" => array(
        array("type" => "payment")
    )
);

$webhook = new MercadoPago\Webhook();
$webhook->url = $webhook_data["url"];
$webhook->event_types = $webhook_data["event_types"];
$webhook->save();

echo "Webhook configurado.";
?>

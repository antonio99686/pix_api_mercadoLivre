<?php
require_once 'vendor/autoload.php'; // Caminho para o autoload do Mercado Pago SDK

// Configure as credenciais do Mercado Pago
MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');


// Crie uma preferência de pagamento
$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = 'Meu produto';
$item->quantity = 1;
$item->unit_price = 75.56;
$preference->items = array($item);

$payer = new MercadoPago\Payer();
$payer->email = 'email_do_comprador@exemplo.com';
$preference->payer = $payer;

$preference->save();

// Exiba o link de pagamento
echo $preference->init_point;
?>

<?php
require __DIR__ . '/vendor/autoload.php';

MercadoPago\SDK::setAccessToken('APP_USR-1228299603673792-062511-2cf7ec6e1d129bd3c26d70331d1b71ab-474362529');

$preference = new MercadoPago\Preference();

$item = new MercadoPago\Item();
$item->title = 'Mensalidade';
$item->quantity = 1;
$item->currency_id = 'BRL';
$item->unit_price = 30.00;

$preference->items = array($item);
$preference->save();

// Redireciona para o pagamento
header('Location: ' . $preference->init_point);
exit;
?>